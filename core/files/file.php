<?


function shortName($file){
    global $progDir;
    if (file_exists($progDir.'\\'.$file))
        return $progDir.'\\'.$file;
    else
        return $file;
}

// расширение файла без символа ".", все переводит в нижний регистр для удобства
// сравнения
function fileExt($file){
    $file = basename($file);
    $k = strrpos($file,'.');
    if ($k===false) return '';
    return strtolower(substr($file, $k+1, strlen($file)-$k-1));
}

// Возвращает true если файл $file расширения $ext, либо его расширение имеется
// в массиве $ext. $ext - массив или строка
function checkExt($file, $ext){
    $file_ext = fileExt($file);
    
    
    if (is_array($ext)){
        foreach ($ext as $item){
            $item = str_replace('.', '', strtolower(trim($item)));
            if ($item == $file_ext) return true;
        }
    } else {
        $ext = str_replace('.', '', strtolower(trim($ext)));
        if ($ext == $file_ext) return true;
    }
    
    return false;
}

// Возвращает название файла без расширения
function basenameNoExt($file){
    $file = basename($file);
    $ext = fileExt($file);
    return str_ireplace('.' . $ext, '', $file);
}


function getFileName($str, $check = true){
    
    if ($check && function_exists('resFile')){
        
        return resFile($str);
    }
    
    $last_s = $str;
    if (!file_exists($str))
        $str = DOC_ROOT .'/'. $str;
        
    if (!file_exists($str))
        $str = $last_s;
    else
        $str = str_replace('/', DIRECTORY_SEPARATOR, $str);
        
    return $str;
}


function findFilesV2($dir, $ext = null, $recursive = false, $with_dir = false)
{
	if (!is_dir($dir)) {
		return array();
	}

	if ($recursive) {
		$iterator = new RecursiveDirectoryIterator($dir);
		$iterator = new RecursiveIteratorIterator($iterator);
	} else {
		$iterator = new DirectoryIterator($dir);
	}
	if ($ext) {
		$iterator = new RegexIterator($iterator, '#^.+\.(' . $ext . ')$#', RecursiveRegexIterator::GET_MATCH);
	}

	$result = array();
	foreach ($iterator as $fileinfo) {
		if (is_array($fileinfo)) {
			$result[] = str_replace('\\', '/', $with_dir ? ($recursive ? $fileinfo[0] : $dir . $fileinfo[0]) : basename($fileinfo[0]));
		} else {
			$result[] = str_replace('\\', '/', $with_dir ? $fileinfo->getPathname() : $fileinfo->getFilename());
		}
	}
	return $result;
}


// поиск файлов в папке... в подпапках не ищет.
// Можно искать по расширению exts - список расширений
function findFiles($dir, $exts = null, $recursive = false, $with_dir = false){
	if (!preg_match('#^phar#', $dir))throw new Exception($dir);
    $dir = replaceSl($dir);
    
    $result = array();
    $check_ext = $exts;
    if (!file_exists($dir)) return array();
    
    if ($handle = @opendir($dir))
        while (($file = readdir($handle)) !== false){
            
            if ($file == '.' || $file == '..') continue;
            if (is_file($dir . '/' . $file)){
                
                if ($check_ext){
                    if (checkExt($file, $exts))
                        $result[] = $with_dir ? $dir .'/'. $file : $file;
                } else {
                    $result[] = $with_dir ? $dir .'/'. $file : $file;
                }
            } elseif ($recursive && is_dir($dir . '/' . $file)){
                
                $result = array_merge($result, findFiles($dir . '/' . $file, $exts, true, $with_dir));
            }
        }
    
    return $result;
}

function findDirsV2($dir)
{
	$result = array();
	foreach (new DirectoryIterator($dir) as $fileinfo) {
		$name = $fileinfo->getFilename();
		if ($fileinfo->getType() == 'dir' && $name != '.' && $name != '..') {
			$result[] = $name;
		}
	}
	return $result;
}


function findDirs($dir){
    throw new Exception($dir);
    $dir = replaceSl($dir);
    
    if (!is_dir($dir)) return array();
    
    $files = scandir($dir);
    array_shift($files); // remove ‘.’ from array
    array_shift($files); // remove ‘..’ from array
    
    $result = array();
    foreach ($files as $file){
        
        if (is_dir($dir .'/'. $file)){
            
            $result[] = $file;
        }
    }
    return $result;
}

function rmdir_recursive($dir) {
    $dir = replaceSl($dir);
    
    if (!is_dir($dir)) return false;
    
    $files = scandir($dir);
    array_shift($files); // remove ‘.’ from array
    array_shift($files); // remove ‘..’ from array
    
    foreach ($files as $file) {
        $file = $dir . '/' . $file;
        if (is_dir($file)) {
            rmdir_recursive($file);
        
        if (is_dir($file))
            rmdir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dir);
}

function deleteDir($dir, $dir_del = true, $exts = null){
    
    $dir = replaceSl($dir);
    $files = findFiles($dir, $exts, true, true);
    
    foreach ($files as $file){
        
        if (file_exists($file))
            unlink($file);
    }
    
    if ($dir_del)
        rmdir_recursive($dir);
}

function include_ex($file){
    
    $file = getFileName($file);
    include_enc($file);
}

function fileLock($file){
    
    $file = getFileName($file);
    $fp = fopen($file, "a");
    flock($fp, LOCK_SH);
    $GLOBALS['__fileLock'][$file] = $fp;
}

function fileUnlock($file){
    
    $file = getFileName($file);
    
    if (isset($GLOBALS['__fileLock'][$file]))
        flock($GLOBALS['__fileLock'][$file], LOCK_UN);
}

function dirLock($dir, $exts = null){
    
    $files = findFiles($dir, $exts, true, true);
    foreach ($files as $file)
        fileLock($file);
}

function dirUnlock($dir, $exts = null){
    $files = findDirs($dir, $exts, true, true);
    foreach ($files as $file)
        fileUnlock($file);
}


function file_p_contents($file, $data){
    
    $file = replaceSl($file);
    $dir  = dirname($file);
    
    if (!file_exists($dir))
        mkdir($dir, 0777, true);
    
    return file_put_contents($file, $data);    
}

function x_copy($from, $to, $skip = false)
{
	$to = replaceSl($to);
	if ($skip && is_file($to)) {
		return true;
	}

	$from = replaceSl($from);
	$dir  = dirname($to);

	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}

	return copy($from, $to);
}

function x_move($from, $to){
    
    $x = 0;
    while (!x_copy($from, $to)){
        if ($x>30){
            break;
        }
        $x++;
    }
    
    $x = 0;
    while (!unlink($from)){
        if ($x>30)
            break;
        $x++;
    }
}