<?

class Loader {


    static function file($file, $add_thread = true){

        $ext = fileExt($file);
            if (!$ext){
                
                if ( file_exists(SYSTEM_DIR . $file.'.phz') )
                    $ext = 'phz';
                elseif ( file_exists(SYSTEM_DIR . $file.'.phb') )
                    $ext = 'phb';
                elseif ( file_exists(SYSTEM_DIR . $file.'.phpe') )
                    $ext = 'phpe';
                elseif ( file_exists($file.'.php') )
                    $ext = 'php';
                        
                $file .= '.'.$ext;
            }
            
        if ($ext){
            
            if (class_exists('Thread') && $add_thread){
                
                if ( $file!='phar://system.phar/modules/animation.php' )
                Thread::addFile($file);
            }            
            
            if ($ext=='phz' || $ext=='phb')
                bcompiler_load(SYSTEM_DIR . $file);
            elseif ($ext=='phpe')
                include_ex(SYSTEM_DIR . $file);
			elseif (!is_file($file))
				throw new Exception('FILE: ' . $file . ' not found');
            else
                require_once($file);
                
            return true;
        }
        throw new Exception('FILE: ' . $file . ' not found');
        return false;
    }

    static function events(TForm &$form, $file = false){
           if ($file)
                self::file('forms/'.$file);
           loadFormEvents($form);
    }

    static function lib($file){
		if (is_file('phar://system.phar/libs/' . $file)) {
			return self::file('phar://system.phar/libs/' . $file);
		} elseif (is_file('phar://system.phar/libs/' . $file . '.php')) {
			return self::file('phar://system.phar/libs/' . $file . '.php');
		} else {
			throw new Exception('LIB: ' . $file . ' not found');
		}
    }
    
    static function inc($file){
        
        if ( fileExt($file)=='phpe2')
            include_ex2($file);
        elseif ( fileExt($file)=='phpe' )
            include_ex($file);
        elseif ( fileExt($file)=='phb')
            bcompiler_load($file);
        elseif (!is_file($file))
			throw new Exception('INC: ' . $file . ' not found');
		else
			include $file;
    }
    
    static function model($file){
		$found = false;
		if (file_exists(SYSTEM_DIR . 'models/'.$file.'.phpe'))
            include_ex(SYSTEM_DIR . 'models/'.$file.'.phpe');
        elseif (file_exists(SYSTEM_DIR . 'models/'.$file.'.phz')){
            bcompiler_load(SYSTEM_DIR .'/models/'.$file.'.phz');
        }
        elseif (file_exists(SYSTEM_DIR . 'models/'.$file.'.phb')){
            bcompiler_load(SYSTEM_DIR .'/models/'.$file.'.phb');
        }
        else {
			$found = true;
            self::file('phar://system.phar/models/' . $file, false);
        }
		if (!$found) {
			throw new Exception('MODEL: ' . $file . ' not found');
		}
        
        $class = 'my'.$file;
        if (class_exists($class))
            if (method_exists($class, 'afterLoad')){
                
                $tmp = new $class;
                $tmp->afterLoad();
                unset($tmp);
            }
    }
    
    static function module($file){
            self::file('phar://system.phar/modules/'.$file);
    }
    
    static function modules($dir)
	{
		foreach (new RegexIterator(new DirectoryIterator('phar://system.phar/' . $dir . '/'), '#^.+\.php$#i', RecursiveRegexIterator::GET_MATCH) as $file) {
			require_once( 'phar://system.phar/' . $dir . '/' . $file[0]);
		}

        /*$files = findFiles(SYSTEM_DIR . $dir, 'php');
        foreach ($files as $file)
            self::file('modules/'. basenameNoExt($file)); */
    }

    static function helper($helper){

            if (self::file('helpers/'.$helper.'.php')){
                $helper[0] = strtoupper($helper[0]);
                $class = 'Helper' . $helper;
                return new $class;
            } else {
                return false;
            }
    }

}