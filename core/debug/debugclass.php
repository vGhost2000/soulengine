<?php

class DebugClassException extends Exception {

}


class DebugClass
{
	public $self = 0;
	public $nameParam = '';
	public $___hide_error_to_log;
	
	public function __construct($name){
		if ( is_numeric($name) )
			$this->nameParam = syncEx('gui_propGet', array($name, 'name'));
		else
			$this->nameParam = $name;
	}


	protected function debug_backtrace($text, $name)
	{
		$text = t($text, $this->nameParam, $name);
		// если включен режим не показывать ошибки
		if ($this->___hide_error_to_log) {
			// если включен дебаг, то пишем в лог, иначе просто игнорируем ошибку
			if (vGDEBUG) {
				ob_start();
				debug_print_backtrace();
				file_put_contents(DOC_ROOT . 'php_err.log', $text . "\n" . ob_get_clean() . "\n\n\n", FILE_APPEND | LOCK_EX);
			}
		} else {
			throw new Exception($text);
		}
	}


	public function __set($name, $value)
	{
		$this->debug_backtrace('Component "%s" not found for set "%s" property', $name);
	}


	public function __get($name)
	{
		$this->debug_backtrace('Component "%s" not found for get "%s" property', $name);
	}


	public function __call($name, $args)
	{
		$this->debug_backtrace('Component "%s" not found for call "%s" method', $name);
	}


	public function valid(){
		return false;
	}
}


class ThreadDebugClass extends DebugClass
{
	public function __set($name, $value)
	{
		$this->debug_backtrace('Change the GUI in the thread forbidden - SET "%s"->"%s" = ...', $name);
	}


	public function __get($name)
	{
		$this->debug_backtrace('Change the GUI in the thread forbidden - GET "%s"->"%s"', $name);
	}


	public function __call($name, $args)
	{
		$this->debug_backtrace('Change the GUI in the thread forbidden - CALL "%s"->"%s()"', $name);
	}
}