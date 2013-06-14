<?php

// Change the GUI in the thread forbidden 

class t
{
	private static $block = array();
	private $name;
	public function __construct($name)
	{
		$this->name = $name;
	}


	public static function c($name, $block = false)
	{
		if ($key = $GLOBALS['THREAD_SELF']) {
			if ($block) {
				$block = ($block < 10 ? 10 : $block) * 1000;
				while(SyncEx('t::block', array($name, $key)) != 'true') {
					usleep($block);
				}
			}

			if (empty($GLOBALS['___t_self'][$name])) {
				$GLOBALS['___t_self'][$name] = new t($name);
			}
			return $GLOBALS['___t_self'][$name];
		} else {
			if (!isset($GLOBALS['___t_objects'][$name])) {
				$GLOBALS['___t_objects'][$name] = c($name);
			}
			return $GLOBALS['___t_objects'][$name];
		}
	}


	public static function unBlock($name)
	{
#		if($GLOBALS['THREAD_SELF']) {
#			Sync('t::unBlock', array($name));
#		} else {
			self::$block[$name] = null;
#		}
	}


	public static function block($name, $id)
	{
		if (!empty(self::$block[$name]) && self::$block[$name] != $id) {
			return 'false';
		}
		self::$block[$name] = $id;
		return 'true';
	}


	public function __get($name)
	{
		return SyncEx('t::get', array($this->name, $name));
	}


	public static function get($name, $property)
	{
		if (!isset($GLOBALS['___t_objects'][$name])) {
			$GLOBALS['___t_objects'][$name] = c($name);
		}
		$GLOBALS['APPLICATION']->ProcessMessages();
		return $GLOBALS['___t_objects'][$name]->$property;
	}


	public function __set($name, $value)
	{
		Sync('t::set', array($this->name, $name, $value));
	}


	public static function set($name, $property, $value)
	{
		if (!isset($GLOBALS['___t_objects'][$name])) {
			$GLOBALS['___t_objects'][$name] = c($name);
		}
		$GLOBALS['___t_objects'][$name]->$property = $value;
		$GLOBALS['APPLICATION']->ProcessMessages();
	}


	public function __call($name, $args)
	{
		return SyncEx('t::call', array($this->name, $name, $args));
	}


	public static function call($name, $method, $args)
	{
		if (!isset($GLOBALS['___t_objects'][$name])) {
			$GLOBALS['___t_objects'][$name] = c($name);
		}
		$res = call_user_func_array(array($GLOBALS['___t_objects'][$name], $method), $args);
		$GLOBALS['APPLICATION']->ProcessMessages();
		return $res;
	}
}



?>