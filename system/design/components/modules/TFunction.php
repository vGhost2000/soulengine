<?php

class TFunction extends __TNoVisual
{

	public $class_name_ex = __CLASS__;
	public $rand;


	public function __inspectProperties()
	{
		return array('parameters','description','toRegister','workBackground','priority','isSync');
	}


	public function __initComponentInfo()
	{
		parent::__initComponentInfo();

		if ($this->callOnStart) {
			$GLOBALS['___startFunctions'][] = 'c('.$this->self.')->call();';
		}

		define('USER_FUNCTION_SELF_'.strtolower($this->name), $this->self);
	}


	public function __construct($onwer=nil,$init=true,$self=nil)
	{
		parent::__construct($onwer, $init, $self);

		if ($init) {
			$this->priority = tpIdle;
			$this->toRegister = true;
		}
	}


	function call()
	{
		if (!$this->onExecute) {
			return null;
		}

		$args  = func_get_args();

		$names = array($this->self, '$names');
		$names = array_merge($names,explode(_BR_,trim($this->parameters)));

		foreach ($names as $i=>$var) {
			$var  = str_replace('$','',$var);

			if ($i>1) {
				$$var = $args[$i-2];
			}
		}

		if (!$names[count($names)-1]) {
			unset($names[count($names)-1]);
		}

		return eval('return '.$this->onExecute . '('.implode(',',$names).');');
	}

	// универсальный метод
	public function __register($form_name, $name, $info, $eventList)
	{
		$prs = $info['parameters'];
		if (strpos($prs, _BR_ ) === false) {
			$names = $prs;
		} else {
			$names = implode(',', explode(_BR_, $info['parameters']));
		}

		if (!$name) {
			$name = $this->name;
		}

		if (is_array($eventList)) {
			$event_code = "\n\n" . $eventList['onexecute'] . "\n\n";
		} else {
			$event_code = "\n\n" . $eventList . "\n\n";
		}


		if (preg_match_all('#\$([a-z0-9_]+)#si', $names, $var_names)) {
			$var_names2 = 'array($' . implode(', $', $var_names[1]) . ')';
			$var_names = 'array("' . implode('", "', $var_names[1]) . '")';
		} else {
			$var_names2 = $var_names = 'array()';
		}

		if ($info['workBackground']) {
			$code = _BR_ . '
				function ___thread_' . $name . '($self)
				{
					' . enc_getValue('__incCode') . ';
					$_thread = TThread::get($self);
					if (!empty($_thread->args) && is_array($_thread->args)) {
						extract($_thread->args);
					}
					' . $event_code . '
				}
				function ' . $name . '(' . $names . ')
				{
					$th = new TThread("___thread_' . $name . '");
					$th->priority = ' . (int)$info['priority'] . ';
					$th->args = compact(' . $var_names . ');
					$th->resume();
					return $th;
				}
			';
		} else {
			if (empty($form_name)) {
				throw new Exception('Function build error: form_name can\'t be empty');
			}
			$code = '
				function ' . $name . '(' . $names . ')
				{
			';
			if ($info['isSync']) {
				$code .= '
					if ($GLOBALS["THREAD_SELF"]) {
						return syncEx("' . $name . '", ' . $var_names2 . ');
					}
				';
			}
			$code .= '
					global $' . strtolower($form_name) . ';
					' . enc_getValue('__incCode') . ';
					' . CodeBuilder::optimizeCode($event_code, '$' . strtolower($form_name) . '->') . '
				}
			';
		}
		return $code;
	}


    function register($name = false){
	
	if (!$name) $name = $this->name;
		
	if (function_exists($name)){
	    //pre('Function "'.$name.'" already exists!');
	} elseif ($this->onExecute) {
	    
	    	$code = __exEvents::getEvent($this->self, 'onexecute');
		$info['parameters'] = $this->parameters;
		$info['workBackground'] = $this->workBackground;
		$info['priority']   = $this->priority;
		
		$code = $this->__register('',$name,$info,$code);
		eval ($code);
	}
    }
    
}

function f($function){
	
	if (!is_object($function)){
	    $function = str_replace(array('.','::'),'->',$function);
	    $func = c($function, true); // cached
	} else {
	    $func =& $function;
	}
	
	if (!$func)
	    return msg('"'.$function.'" - function not found!');
	
	
	$args = func_get_args();
	unset($args[0]);
	$args = array_values($args);
	
	$names = array();
	foreach ($args as $i=>$var){
	    $var     = 'var'.$i;
	    $$var    = $args[$i];
	    $names[] = '$'.$var;
	}
	
	return eval('return $func->call(' . implode(',',$names) . ');');
}

function __callFunction($function, $self){
    
    $function = str_replace(array('.','::'),'->',$function);
    $func = c($function, true); // cached
    
	if (!$func)
	    return msg('"'.$function.'" - function not found!');
	
	$func->parameters = '$self'._BR_.'$obj';
    
    return eval('return $func->call(' . $func->self .',_c('. $self . '));');
}

?>