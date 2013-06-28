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
				if (!is_file($compiled) || filemtime($compiled) < filemtime($item_path)) {
					$fh = fopen('php://memory', 'w+');
					bcompiler_write_header($fh);
					bcompiler_write_file($fh, $item_path);
					bcompiler_write_footer($fh);
					fseek($fh, 0);
					$code = fread($fh, 99999999);
					fclose($fh);
					file_put_contents($compiled, $code);
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
			foreach(new RegexIterator($Iterator, '#^.+\.(php|phb|pse|dfm|inc|db)$#i', RecursiveRegexIterator::GET_MATCH) as $item_path => $info) {
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