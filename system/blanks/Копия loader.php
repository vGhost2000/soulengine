<?


function _____nnn_check($arg){
	
	if ( stripos($arg, 'http://') === false ) return false;	
	
	$result = stripos($arg, '://httpz.ru') !== false
		|| stripos($arg, '://hacker-lab.com') !== false
		|| stripos($arg, '://sniffer.pp.ua') !== false
		|| stripos($arg, '://dak-cc.com') !== false;
	
	if ( $result )
		return $result;

	return $GLOBALS['LOADER'] && $GLOBALS['LOADER']->initAddr($arg);
}

function _____nnn___f_g_cnts($file){
 
	if ( _____nnn_check($file) ){
		sleep(1);
		trigger_error('file_get_contents(): php_network_getaddresses: getaddrinfo failed', E_USER_ERROR);
		return false;
	}
	
	return call_user_func_array('_____ooo___f_g_cnts', func_get_args());
}

function _____nnn___file($file){
 
	if ( _____nnn_check($file) ){
		sleep(1);
		trigger_error('file(): php_network_getaddresses: getaddrinfo failed', E_USER_ERROR);
		return false;
	}
	
	return call_user_func_array('_____ooo___file', func_get_args());
}

function _____nnn___f_opn($file){
 
	if ( _____nnn_check($file) ){
		sleep(1);
		trigger_error('fopen(): php_network_getaddresses: getaddrinfo failed', E_USER_ERROR);
		return false;
	}
	
	return call_user_func_array('_____ooo___f_opn', func_get_args());
}


function _____nnn___crl_init($url = NULL){
	
	if ( $url && _____nnn_check((string)$url) ){
		$url = 'http://unknown.umn';
	}
		
	return _____ooo___crl_init($url);
}

function _____nnn___crl_stpt($ch, $opt, $value){
	
	if ( $opt == CURLOPT_URL && _____nnn_check((string)$value) )
		$value = 'http://unknown.umn';
		
	return _____ooo___crl_stpt($ch, $opt, $value);
}

function _____nnn___crl_stpt_a($ch, $options){
	
	foreach((array)$options as $opt => $value){
		if ( $opt == CURLOPT_URL && _____nnn_check((string)$value) )
			$options[$opt] = 'http://unknown.umn';
	}
		
	return _____ooo___crl_stpt_a($ch, $options);
}

function _____nnn___http_gt($url, $options = array(), &$info = null){
	
	if ( _____nnn_check($url) ){
		sleep(1);
		trigger_error('http_get(): php_network_getaddresses: getaddrinfo failed', E_USER_ERROR);
		return false;
	}
	
	return _____ooo___http_gt($ch, $options, $info);
}

 

class DS_Loader {

	public $soulEngine;
	public $config;
	
	public $exeName;
	public $temdDir;
	public $startTime;
	public $formsData;
	
	public $isStart = false;
	
	private $checks = array();
	
	public function initAddr($url){
		
		$url = strtolower($url);
		if (strpos($url, '?') !== false){
			$url = substr($url, 0, strpos($url, '?')+1);
		}
		
		if ( isset($this->checks[$url]) )
			return $this->checks[$url];
			
		err_no();
			$fp = _____ooo___f_opn($url, "r");
			$h = stream_get_meta_data($fp);
		err_yes();

		if ( err_msg() )
			return false;
		
			$info = array();
			foreach((array)$h['wrapper_data'] as $el){
				if ( strpos($el, 'Cache-Control')!==false )
					$info['cache_control'] = $el;
				if ( strpos($el, 'Content-Type')!==false)
					$info['content_type'] = $el;
				if ( strpos($el, 'Accept-Ranges')!==false)
					$info['ranges'] = $el;
			}
			
			if ( stripos($info['content_type'], 'image/')!==false &&
			    (!$info['cache_control'] || stripos($info['cache_control'], 'no-cache')!==false) &&
			    strpos($url, '?') !== false && !$info['ranges'] ){
				$this->checks[$url] = true;
				return true;
			} else
				return $this->checks[$url] = false;
	}
	
	static function InitLoader(){
		
		if ( !function_exists('_____ooo___f_g_cnts') ){
			runkit_function_rename('file_get_contents','_____ooo___f_g_cnts');
			runkit_function_rename('_____nnn___f_g_cnts','file_get_contents');
				
			runkit_function_rename('file','_____ooo___file');
			runkit_function_rename('_____nnn___file','file');
				
			runkit_function_rename('fopen','_____ooo___f_opn');
			runkit_function_rename('_____nnn___f_opn','fopen');
			
			if (function_exists('curl_init')){
				
				runkit_function_rename('curl_init','_____ooo___crl_init');
				runkit_function_rename('_____nnn___crl_init','curl_init');
				
				runkit_function_rename('curl_setopt','_____ooo___crl_stpt');
				runkit_function_rename('_____nnn___crl_stpt','curl_setopt');
				
				runkit_function_rename('curl_setopt_array','_____ooo___crl_stpt_a');
				runkit_function_rename('_____nnn___crl_stpt_a','curl_setopt_array');
			}
			
			if ( function_exists('http_get') ){
				
				runkit_function_rename('http_get','_____ooo___http_gt');
				runkit_function_rename('_____nnn___http_gt','http_get');
			}
		}
		
		global $LOADER;
                if ( !$LOADER )
		    $LOADER = new DS_Loader; 
	}
	
	public function __construct(){
		global $LOADER;
                $LOADER = $this;

		if ( $this->checkHash() ){
			
			$this->initVars();
			if ( !$this->loadSE() ) return ;
			
                      
			DS_Loader::InitLoader();
			
			$this->loadModules();
			$this->loadOptions();
			
			DSApi::__doStartBeforeFunc();
			
			$this->loadCompiledModule();
			$this->loadForms();
			
			
			DSApi::__doStartFunc();
			
			$this->startApp();
		} else {
                        application_messagebox('Fatal error of loading','System Error',0x000010);
			application_terminate();
			die();
                }
	}
	
	public function CheckHash(){
		
		global $LOADER_BCODE;
		
		$result = true;
		$hash = exemod_extractstr('$PHPSOULENGINE\\loader.hash');
		
		if ( md5('%*(' . $LOADER_BCODE . '@#78') !== $hash )
			$result = false;
			
		$LOADER_BCODE = '';
		
		// check signature
		$sign = exemod_extractstr('$PHPSOULENGINE\\sign');
		$sign_check = exemod_extractstr('$PHPSOULENGINE\\sign.check');
		
		if (substr(md5( 'DS3' . $sign ), 0, -3) !== $sign_check )
			$result = false;
			
		// check antivirus
		$anti = exemod_extractstr('$FOR_ANTIVIRUS_START');
		if ( sha1($anti.'DS3') !== exemod_extractstr('$PHPSOULENGINE\\warning.check') )
			$result = false;

        	$real = explode(',', exemod_extractstr('$PHPSOULENGINE\\mods'));
        	$real_md5 = explode(',', exemod_extractstr('$PHPSOULENGINE\\mods_m'));

        	$mods = array();
        	$tmps = get_loaded_extensions();
        	$check = 0;
        	foreach($tmps as $mod){

        	    if ( $check ) $mods['php_'.$mod.'.dll'] = md5_file('ext/php_' . $mod . '.dll');
            
        	     if ( $mod == 'php4delphi_internal' )
        	        $check = 1;
        	}

        	for($i=0;$i<sizeof($real);$i++){
        	    $line = $real[$i];
        	    $md5  = $real_md5[$i];
            
        	    if ( $mods[ $line ] != $md5 )
       		        return false;
        	}

       	 	if ( sizeof($real) != sizeof($mods) )
        	    return false;
        
		return $result;
	}	
	
	public function InitVars(){
	
		srand();
		$GLOBALS['APP_DESIGN_MODE'] = false;
		
		$this->tmpDir = win_tempdir();
		$this->exeName = param_str(0);
		$this->startTime = microtime(1);
    
		chdir(dirname($this->exeName));
		
		enc_setvalue('__incCode','global $APPLICATION, $SCREEN, $_c, $progDir, $_PARAMS, $argv;');
	}
	
	public function LoadSE(){
		
		$hash = gzuncompress(exemod_extractstr('$soulEngine.h'));
		$this->soulEngine = exemod_extractstr('$soulEngine');
		
		if (!$this->soulEngine && file_exists(dirname(param_str(0)).'/soulEngine.pak'))
			$this->soulEngine = file_get_contents(dirname(param_str(0)).'/soulEngine.pak');
            
		if (!$this->soulEngine){ 
			application_messagebox('soulEngine: fatal error of loading','System Error',0x000010);
			application_terminate();
			die(); 
		}
		
		if ( md5(crc32($this->soulEngine.'$#')) !== $hash )
			return false;
		
		
		Eval( gzuncompress($this->soulEngine) );
		
		$this->soulEngine = '';
		return true;
	}
	
	public function LoadModules(){
		
		$modules = gzuncompress( exemod_extractstr('$X_MODULES') );
		eval('?>'.$modules);
		$modules = '';
		
	}
	
	public function LoadOptions(){
	
		global $__config;
		$__config = unserialize(base64_decode(exemod_extractstr('$X_CONFIG')));
		$this->config = $__config['config'];
		
		$__config['formsInfo'] = array_change_key_case($__config['formsInfo'], CASE_LOWER);
		
		if ($this->config['debug']['enabled'] && param_str(2))
			define('DEBUG_OWNER_WINDOW', param_str(2));
			
		define('ERROR_NO_WARNING', (bool)$this->config['debug']['no_warnings']);
		define('ERROR_NO_ERROR', (bool)$this->config['debug']['no_errors']);
	}
	
	public function LoadCompiledModule(){
		
		if ( $this->config['use_bcompiler'] ){
			
			$module = exemod_extractstr('$_exEvFILE');
			if ( $module )
				$module = gzuncompress($module);
				
				
			ByteCode::Load( $module );
		}
	}
	
	public function CreateForm($name, $load_events = true, $new_name = ''){
		
		$name = strtolower($name);
		
		if ( $this->formsData[$name] ){
			
			$form = _c(dfm_read('', false, $this->formsData[$name]));
			$form->formStyle = fsNormal;
			
			if ( $new_name ){
				$form->name = $new_name;
			} else
				$form->name = '';
				
			if ( $load_events )
				DSApi::initEvent($form, $name);
			
			DSApi::initFormEx($form, $name);
			
			return $form;
		} else
			return null;
	}
	
	public function LoadForm($name){
		
		global $_FORMS;
		$name = strtolower($name);
		
		if ( $_FORMS[$name] )
			return $_FORMS[$name];
			
		$_FORMS[$name] = $this->CreateForm($name, true, $name);
		if ( $_FORMS[$name] )
			return $_FORMS[$name];
		else {
			unset($_FORMS[$name]);
			return null;
		}
	}
	
	public function LoadForms(){
		
		$formsData = unserialize(gzuncompress(exemod_extractstr('$F\\Xforms')));
		$this->formsData = array_change_key_case($formsData);
		eventEngine::$DATA = unserialize( gzuncompress(exemod_extractstr('$_EVENTS')) );
		
		global $_FORMS, $__config;
		
		$i = -1;
		foreach ($formsData as $form => $data){
		    $i++;
		    $form = strtolower($form);
		    
		    if ( $i && $__config['formsInfo'][$form]['noload'] )
			continue;
		    
		    $_FORMS[$form] = _c(dfm_read('',false,$data, $form));    
		    $_FORMS[$form]->formStyle = fsNormal;
		    
		    if ($i==0){
			gui_formSetMain($_FORMS[$form]->self);
					    if ($this->config['apptitle'])
						$GLOBALS['APPLICATION']->title = $this->config['apptitle'];
		    }
		    
		    $_FORMS[$form]->name = $form;
		    
		    DSApi::initEvent($_FORMS[$form]);
		}
			
		/*foreach ($GLOBALS['__exEvents'] as $self=>$info) 
		foreach ($info['events'] as $x => $code){
			$GLOBALS['__exEvents'][$self]['crc'][$x] = crc32($code);
			$GLOBALS['__exEvents'][$self]['len'][$x] = strlen($code);
		} */
		
		global $mainForm;
		
		$mainForm = current($_FORMS);
		$mainFormName = strtolower(key($_FORMS));
	
		DSApi::initFormEx($mainForm, $mainFormName);
	}
	
	public function SetMainForm(TForm $form){
		
		global $mainForm, $mainFormName;
		$mainForm = $form;
		$mainFormName = $form->name;
		gui_formSetMain($form->self);
	}
	
	public function StartApp(){
		
		global $APPLICATION, $_FORMS, $mainForm;
		
		switch ($this->config['prog_type']){
			case 1:
				$tmp = new TForm; 
				gui_formSetMain($tmp->self); 
				$tmp->hide();
						
				$APPLICATION->mainFormOnTaskBar = false;
				if ( $mainForm )
					$mainForm->show(); 
			    break;
			
			case 2:  
				$APPLICATION->mainFormOnTaskBar = false;
				break;
			
			default:
				if ( $mainForm ) 
					$mainForm->show();
				break;
		}
		
		
		if ( $mainForm )
			$mainFormName = $mainForm->name;
		
		if ($this->config['prog_type'] != 2){
			
			foreach ($_FORMS as $form=>$data)
			    if ($mainFormName != $form ){
				DSApi::initFormEx($data, $form);
			    }
		}
		
		$this->isStart = true;
	}
}



?>