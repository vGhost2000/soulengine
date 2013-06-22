<?

/*
 
    PHP Soul Engine Error Hooker
    
    2009.04 ver 0.2
    
    Main function:
        __error_hook(type, filename, line, msg)
        
    // перехватчик ошибок...
    
*/


function errors_init(){
    
    $GLOBALS['__show_errors'] = true;
    $old_error_handler = set_error_handler("userErrorHandler");
    set_fatal_handler("userFatalHandler");
}


// определяемая пользователем функция обработки ошибок
function userErrorHandler($errno = false, $errmsg = '', $filename='', $linenum=0, $vars=false, $eventInfo=false)
{
    
    if ($errno == E_NOTICE || $errno == E_DEPRECATED) return;
    if ($errno == 2048) return;
    
    if ( $eventInfo ){    
        $GLOBALS['__eventInfo'] = $eventInfo;
    }
    
   
    /*if ($errno === false){
        
        $prs = v('__'.__FUNCTION__);
        
        $errno = $prs[0];
        $errmsg = $prs[1];
        $filename = $prs[2];
        $linenum = $prs[3];
        
        $GLOBALS['__eventInfo'] = v('__eventInfo');
        
    }*/
    
    //c('form1')->text = $GLOBALS['THREAD_SELF'];
    
    
    // pre();
    if (defined('ERROR_NO_WARNING') && ERROR_NO_WARNING/* === true*/){
        if ($errno == E_WARNING || $errno == E_CORE_WARNING || $errno == E_USER_WARNING) return;
    }
    
    if (defined('ERROR_NO_ERROR') && ERROR_NO_ERROR/* === true*/){
        if ($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_USER_ERROR) return;    
    }
    
    if ( $errno == E_USER_ERROR && !$eventInfo ){
        
        $info = debug_backtrace();
        next($info);
        $info = next($info);
        $linenum = $info['line'];
    }
     
    // for threading...
    if ($GLOBALS['__show_errors'] && $GLOBALS['THREAD_SELF']){
        
        if (sync('userErrorHandler', array($errno, $errmsg, $filename, $linenum, false, $GLOBALS['__eventInfo'])))
            return;
    }
    
    
    //pre($errmsg);
    $GLOBALS['__error_last'] = array(
                                     'msg'=>$errmsg,
                                     'file'=>$filename,
                                     'line'=>$linenum,
                                     'type'=>$errno,
                                     );
    
    if (!$GLOBALS['__show_errors'] /*|| v('is_showerror')*/) return;
    
    //v('is_showerror', true);
    // 
    global $__eventInfo;
    
    $errortype = array (
                0                 => "Fatal Error",
                E_ERROR           => "Error",
                E_WARNING         => "Warning",
                E_PARSE           => "Parsing Error",
                E_NOTICE          => "Notice",
                E_CORE_ERROR      => "Core Error",
                E_CORE_WARNING    => "Core Warning",
                E_COMPILE_ERROR   => "Compile Error",
                E_COMPILE_WARNING => "Compile Warning",
                E_USER_ERROR      => "User Error",
                E_USER_WARNING    => "User Warning",
                E_USER_NOTICE     => "User Notice",
                E_STRICT          => "Runtime Notice"
    );
    
    $type = $errortype[$errno];
    
    
    if (defined('DEBUG_OWNER_WINDOW')){
                
        $result['type'] = 'error';
        $result['script'] = $filename;
        $result['event']  = $__eventInfo['name'];
        $result['name'] =  __exEvents::getEventInfo($__eventInfo['self']);
        $result['msg']  = $errmsg;
        $result['errno']= $errno;
        $result['errtype'] = $type;
        $result['line'] = $linenum;
        
        if ( is_array($vars) )
            $result['vars'] = array_keys($vars);
        
        application_minimize();
        
        Receiver::send(DEBUG_OWNER_WINDOW, $result);
        
        application_restore();
        $GLOBALS['APPLICATION']->toFront();
        return;
    }
    
    $arr[]= '['.$type.']';
    $arr[]= t('Message').': "' . $errmsg . '"';
    
    if (file_exists($filename)){
        $arr[]= ' ';
        
        if (defined('EXE_NAME'))
            $filename = str_replace(replaceSr(dirname(replaceSl(EXE_NAME))),'',$filename);
        
        $arr[] = $filename;
        $arr[] = t('On Line').': ' . $linenum;
    }
    
    if ($__eventInfo){
        
        $arr[] = ' ';
        $arr[] = '['.t('EVENT').']';
        if ($__eventInfo['name'])
            $arr[] = t('Type').': '.$__eventInfo['name'];
            
        if ($__eventInfo['obj_name'])
            $arr[] = t('Object').': "' .$__eventInfo['obj_name'].'"';
    }
    
    $arr[] = ' ';
    $arr[] = '.:: '.t('To abort application?').' ::.';
    
    $str = implode(_BR_, $arr);
    
    message_beep(MB_ICONERROR);
    $old_error_handler = set_error_handler("userErrorHandler");
    
    switch (messageDlg($str, mtError, MB_OKCANCEL)){
        
        case mrCancel: return true;
        case mrOk: application_terminate(); return false; break;
    }
    return;
}

function userFatalHandler($errno = false, $errmsg = '', $filename='', $linenum=0){
    
    userErrorHandler($errno, $errmsg, $filename, $linenum);
}

function error_message($msg){
    messageBox($msg, appTitle() . ': Error', MB_ICONERROR);
    die();
}

function error_msg($msg){
    messageBox($msg, appTitle() . ': Error', MB_ICONERROR);
}

function __error_hook($type, $filename, $line, $msg){
    error_message("'$msg' in '$filename' on line $line");
}

function checkFile($filename){
	throw new Exception($dir);
    $filename = str_replace('//','/',replaceSl($filename));
    
    if (!file_exists(DOC_ROOT . $filename) && !file_exists($filename)){
        error_message("'$filename' is not exists!");
        die();
    }
}

function checkFileV2($filename)
{
	if (!file_exists($filename) && !file_exists(DOC_ROOT . $filename)){
		error_message("'$filename' is not exists!");
		die();
	}
}

function err_no(){
    $GLOBALS['__show_errors'] = false;
    $GLOBALS['__error_last']  = false;
}

function err_status($value = null, $force = false){
    
    $GLOBALS['__error_last']  = false;
    if ($value===null)
        return $GLOBALS['__show_errors'];
    else{ if (!$force && !vGDEBUG)$value=false;
        $res = $GLOBALS['__show_errors'];
        $GLOBALS['__show_errors'] = $value;
        return $res;
    }
}

function err_yes(){
    $GLOBALS['__show_errors'] = true;
    $GLOBALS['__error_last']  = false;
}

function err_msg(){
    return $GLOBALS['__error_last']['msg'];
}

function err_last(){
    return $GLOBALS['__error_last'];
}

errors_init();

/* fix errors */
err_no();
    date_default_timezone_set(date_default_timezone_get());
    ini_set('date.timezone', date_default_timezone_get());
err_yes();


function __dt()
{
	if (vGDEBUG) {
		$args = func_get_args();
		if ($args) {
			$args = print_r($args, true);
		} else {
			$args = 'no args';
		}
		ob_start();
		debug_print_backtrace();
		$text = $args . "\n" . ob_get_clean();
		file_put_contents(DOC_ROOT . 'php_err.log', $text . "\n\n\n", FILE_APPEND | LOCK_EX);
		pre($text);
	}
}
