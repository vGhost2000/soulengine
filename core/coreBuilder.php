<?php

//gui_message('working');
//gui_message($engineDir);

function nix($s){return str_replace("\\","/",$s);}

class CoreBuilder
{
	private final static function buildReplacers()
	{
		try {
			list($dir, $prog) = self::getPathVars();
			$secret = $prog . 'secret/';
			$Directory = new RecursiveDirectoryIterator($secret);
			$Iterator  = new RecursiveIteratorIterator($Directory);
			foreach(new RegexIterator($Iterator, '#^.+\.(php)$#i', RecursiveRegexIterator::GET_MATCH) as $item_path => $info) {
				$path = str_replace($secret, '', nix($item_path));
				$compiled = $prog . $path . 'b';

				// проверим наличие реплейсеров и подставим их если надо
				$replaicers = preg_replace('#\.php$#', '.blank', $item_path);
				$is_replace = is_file($replaicers);

				if (!is_file($compiled) || filemtime($compiled) < filemtime($item_path) || 
					($is_replace && filemtime($compiled) < filemtime($replaicers))
				) {
					
					if ($is_replace) {
						// на всякий случай делаем бекапы шаблонов и читаем код файлов
						copy($item_path, $item_path . '.' . time() . '.bkf');
						$src = $bkf = file_get_contents($item_path);
						$rep = file_get_contents($replaicers);

						// получаем все исходные строки которые надо подставить в шаблон
						preg_match_all('#ZZZZZZZZZZZZZZZZZZZZZZZ \{START ([A-Z_]+)\} ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ(.+)' .
							'ZZZZZZZZZZZZZZZZZZZZZZZ \{END [A-Z_]+\} ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ#Us',
							$rep, $match
						);

						// "шифруем" подставляем данные в шаблон 
						foreach ($match[1] as $mk => $key) {
							if (preg_match('#/\* REPLAICER FOR \{' . $key . '\}\-\{([A-Za-z0-9_]+)\} \*/#', $src, $m)) {
								// генерим ключ для отхоривания (в будущем можно будет применить mcrypt
								$secret_key = md5(rand(1,999999999) . '_' . microtime(1) . '_' . rand(1,999999999));
								$res = array();
								for ($i = 0; $i < 32; $i++) {
									$res[] = 'chr(' . ord($secret_key[$i]) . ')';
								}

								// "шифруем" данные
								$cur = 0;
								$len = strlen($match[2][$mk]);
								for ($i = 0; $i < $len; $i++) {
									$match[2][$mk][$i] = $match[2][$mk][$i] ^ $secret_key[$cur];
									$cur++;
									if ($cur >= 32) {
										$cur = 0;
									}
								}

								// вставляем шифрованные данные на место шаблона в компилируемый файл
								$code = '$' . $m[1] . ' = self::strxor(base64_decode("' . base64_encode($match[2][$mk]) . '"), ' . implode(' . ', $res) . ");\n";
								$src  = str_replace($m[0], $code, $src);
							}
						}
						file_put_contents($item_path, $src);
					}

					// компилируем код в байткод
					$fh = fopen('php://memory', 'w+');
					bcompiler_write_header($fh);
					bcompiler_write_file($fh, $item_path);
					bcompiler_write_footer($fh);
					fseek($fh, 0);
					$code = fread($fh, 99999999);
					fclose($fh);
					file_put_contents($compiled, $code);

					// восстанавливаем оригинальный файл на место
					if ($is_replace) {
						file_put_contents($item_path, $bkf);
					}
				}
			}
		} catch (Exception $e) {
			if (vGDEBUG) {
				gui_message($e->getMessage());
			} else {
				throw $e;
			}
			return false;
		}
		return true;
	}


	public static function getPathVars()
	{
		static $dir, $prog;
		if (!$dir) {
			global $engineDir, $progDir;

			if (empty($engineDir) || !is_dir($engineDir) || empty($progDir) || !is_dir($progDir)) {
				//return;
				throw new Exception('Engine dir (' . $engineDir . ') or Prog dir (' . $progDir . ') not found!');
			}

			$dir  = str_replace('\\', '/', $engineDir);
			$prog = str_replace('\\', '/', $progDir);
			if ($dir[strlen($dir) - 1] != '/') {
				$dir .= '/';
			}
			if ($prog[strlen($prog) - 1] != '/') {
				$prog .= '/';
			}
		}
		return array($dir, $prog);
	}


	public static function buildFrameWork($check_changes = false)
	{
		self::buildReplacers();
		try {
			list($dir, $prog) = self::getPathVars();

			if ($check_changes) {
				$check_changes = filemtime($prog . 'core.phar');
			}
			$archive   = new Phar($prog . 'core.phar');
			$Directory = new RecursiveDirectoryIterator($dir);
			$Iterator  = new RecursiveIteratorIterator($Directory);
			foreach(new RegexIterator($Iterator, '#^.+\.p(hp|hb|se)$#i', RecursiveRegexIterator::GET_MATCH) as $item_path => $info) {
				$item_path = str_replace('\\', '/', $item_path);

				// если это проверка изменений в существующем архиве и дата изменения архива больше даты изменения файла, то файл не менялся, скипаем
				if ($check_changes && $check_changes > filemtime($item_path)) {
					continue;
				}

				$archive->addFile($item_path, str_replace($dir, '', $item_path));
				/*$tmp = $item_path . '.tmp';
				if (is_file($tmp)) {
					unlink($tmp);
				}
				$fh = fopen($tmp, 'w');
				bcompiler_write_header($fh);
				bcompiler_write_file($fh, $item_path);
				bcompiler_write_footer($fh);
				fclose($fh); 
				$archive->addFile($tmp, substr(str_replace($dir, '', $item_path), 0, -1) . 'b');
				unlink($tmp);
				// */
			}
			$archive->setDefaultStub('include.php');
			$archive = null;
		} catch (Exception $e) {
			$archive = null;
			if (is_file($prog . 'core.phar')) {
				unlink($prog . 'core.phar');
			}
			return false;
		}
		return true;
	}


	public static function buildSystemIDE($check_changes = false)
	{
		static $checked;
		if ($checked) {
			return true;
		}
		$checked = true;

		global $APPLICATION;

		$f = new TForm($APPLICATION);
		$f->w = 600;
		$f->h = 70;
		$f->position = 'poScreenCenter';
		$f->BorderStyle = 'bsNone';
		$f->show();

		$i = new TLabel($f);
		$i->parent = $f->self;
		$i->w = 580;
		$i->h = 20;
		$i->left = 10;
		$i->top = 10;
		$i->caption = 'Building System IDE archive:';
		$i->visible = true;
		
		$l = new TLabel($f);
		$l->parent = $f->self;
		$l->w = 580;
		$l->h = 20;
		$l->left = 10;
		$l->top = 40;
		$l->visible = true;

		self::buildReplacers();
		try {
			list($dir, $prog) = self::getPathVars();

			if ($check_changes) {
				$check_changes = filemtime($prog . 'system.phar');
			}
			$archive   = new Phar($prog . 'system.phar');
			$prog      = $prog . 'system/';
			$Directory = new RecursiveDirectoryIterator($prog);
			$Iterator  = new RecursiveIteratorIterator($Directory);
			$archive->startBuffering();
			foreach(new RegexIterator($Iterator, '#^.+\.(php|phb|pse|dfm|inc|db|phpb)$#i', RecursiveRegexIterator::GET_MATCH) as $item_path => $info) {
				$item_path = str_replace('\\', '/', $item_path);

				// если это проверка изменений в существующем архиве и дата изменения архива больше даты изменения файла, то файл не менялся, скипаем
				if ($check_changes && $check_changes > filemtime($item_path)) {
					continue;
				}

				//$archive->addFile($item_path, str_replace($dir, '', $item_path));
				$archive[str_replace($prog, '', $item_path)] = file_get_contents($item_path);
				$l->caption = $item_path;
				$APPLICATION->processMessages();
			}
			$archive->setDefaultStub('include.pse');
			$archive->stopBuffering();
			$archive = null;
			$return  = true;
		} catch (Exception $e) {
			$archive = null;
			if (is_file($prog . 'system.phar')) {
				unlink($prog . 'system.phar');
			}
			//gui_message($e->getMessage());
			$return = false;
		}
		$i->free();
		$l->free();
		$f->free();
		return $return;
	}
}



?>