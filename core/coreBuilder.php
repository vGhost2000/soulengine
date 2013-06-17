<?php

//gui_message('working');
//gui_message($engineDir);


class CoreBuilder
{
	public static function buildFrameWork()
	{
		global $engineDir, $progDir;

		if (empty($engineDir) || !is_dir($engineDir) || empty($progDir) || !is_dir($progDir)) {
			return;
		}

		$dir  = str_replace('\\', '/', $engineDir);
		$prog = str_replace('\\', '/', $progDir);
		if ($dir[strlen($dir) - 1] != '/') {
			$dir .= '/';
		}
		if ($prog[strlen($prog) - 1] != '/') {
			$prog .= '/';
		}

		try {
			$archive   = new Phar($prog . 'core.phar');
			$Directory = new RecursiveDirectoryIterator($dir);
			$Iterator  = new RecursiveIteratorIterator($Directory);
			foreach(new RegexIterator($Iterator, '#^.+\.ph(p|b|e)$#i', RecursiveRegexIterator::GET_MATCH) as $item_path => $info) {
				$item_path = str_replace('\\', '/', $item_path);
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
}


?>