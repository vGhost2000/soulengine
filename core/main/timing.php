<?
/*
  
  SoulEngine Timing Library
  
  2011 ver 3
  
  Dim-S Software (c) 2011
		
		classes:
		TTimer,TTimerEx
		
		functions:
		setTimer, setTimeout
		
  Библиотека для для работы с таймерами и тайм линиями.
  
*/




class TTimer extends TControl{
	public $class_name = __CLASS__;
}

class TTimerEx extends TPanel{
	
	public $class_name_ex = __CLASS__;
	#public $time_out = true;
	public $_timer;
	#public $var_name = ''; // название переменной которая освобождается после отработки таймера
	#public $func_name = ''; // название функции которую нужно выполнить после отработки таймера
	#public $func_arguments = array(); // аргументы функции...
	#public $eval_str = '';
	
	#event onTimer 
	
	static function doTimer($self){
		
		$self = gui_owner($self);
		$props = TComponent::__getPropExArray($self);
		
		// надо сразу избавляться от продолжения таймера, иначе баг =)
		if ($props['time_out']){
			$obj = _c($self);
			$obj->timer->enabled = false;
		}
		
		if ($props['ontimer']){
				eval($props['ontimer'] . '('.$self.');');
		}
		
		if ($props['func_name']){
			
			
			if ($props['checkresult']){
				eval('$result = '.$props['func_name'] . ';');
				if ( $result===true ){
					
					$obj = _c($self);
					//$obj->timer->enabled = false;
					$obj->free();
				}
			}
			else
				eval($props['func_name'] . ';');
		}
		
		if ($props['freeonend']){
			
			$obj->free();
		}
	}
	
	public function __construct($onwer=nil, $init=true, $self=nil){
		parent::__construct($onwer,$init,$self);
		
		if ($init){
			$this->timer->enabled = false;
		}
		
		$this->__setAllPropEx();
	}
	
	function get_timer(){
		
		if (!$this->timer_self){
			$this->_timer = new TTimer($this);
			$this->_timer->name = 'timer';
			$this->_timer->onTimer = 'TTimerEx::doTimer';
			$this->timer_self = $this->_timer->self;
		} else {
			$this->_timer = c($this->timer_self);
		}
		
		return $this->_timer;
	}
	
	public function set_enable($v){
		$this->timer->enabled = $v;
	}
	
	public function get_enable(){
		return $this->timer->enabled;
	}
	
	public function set_enabled($v){
		$this->enable = $v;
	}
	
	public function get_enabled(){
		return $this->enable;
	}
	
	public function set_interval($v){
		$this->timer->interval = $v;
	}
	
	public function get_interval(){
		return $this->timer->interval;
	}
	
	public function get_repeat(){
		return !$this->time_out;
	}
	
	public function set_repeat($v){
		$this->time_out = !$v;
	}
	
	public function start(){
		$this->enabled = true;
		
	}
	
	public function stop(){
		$this->enabled = false;
	}
	
	public function pause(){
		$this->enabled = !$this->enabled;
	}
	
	public function go(){$this->start();}
}


// аналог функции setTimeout из Javascript
// тайминг выполняется единожды...
function setTimeout($interval,$func){
	
	$timer = new TTimerEx();
	$timer->interval  = $interval;
	$timer->func_name = $func;
	$timer->time_out  = true;
	$timer->freeOnEnd = true;
	$timer->enable = true;
	return $timer;
}

// аналог функции setTimer
function setTimer($interval,$func){
	
	$timer = new TTimerEx();
	$timer->interval  = $interval;
	$timer->func_name = $func;
	$timer->time_out  = false;
	$timer->background = $background;
	$timer->enable = true;
	//pre($func);
	return $timer;
}

function setTimerEx($interval,$func){
	$tim = setTimer($interval, $func);
	$tim->checkResult = true;
	return $tim;
}

function setInterval($interval, $func, $background = false){
	return setTimer($interval, $func, $background);
}

function setBackTimeout($interval, $func){
	return setTimeout($interval, $func, true);
}

function setBackTimer($interval, $func){
	return setTimeout($interval, $func, true);
}

class Timer {
	
	static $exec = array();
	static $data = array();
	static $free = array();
	
	static function createTimer(){
		
		$result = 0;
		foreach(Timer::$free as $timer => $busy){
			if ( !$busy ){
				$result = $timer;
				break;
			}
		}
		
		if ( !$result )
			$result = gui_create('TTimer', null);
		
		Timer::$free[ $result ] = true;
		return $result;
	}
	
	static function setInterval($func, $interval){
		
		$result = Timer::createTimer();
		Timer::setIntervalTime($result, $interval);
		
		$myfunc = function($self) use ($func){
			Timer::$exec[ $self ] = true;
			
			call_user_func($func, $self);
			
			Timer::$exec[ $self ] = false;
		};
		
		event_set( $result, 'OnTimer', $myfunc );
		
		return $result;
	}
	
	static function setTimeout($func, $interval){
		
		$result = Timer::createTimer();
		Timer::setIntervalTime($result, $interval);
		
		$myfunc = function($self) use ($func){
			Timer::$exec[ $self ] = true;
			
			call_user_func($func, $self);
			
			Timer::removeData( $self );
			gui_propSet( $self, 'enabled', false );
			//gui_safeDestroy( $self );
			
			Timer::$exec[ $self ] = false;
		};
		event_set( $result, 'OnTimer', $myfunc );
		
		return $result;
	}
	
	static function clearTimer($timer){
		
		if ( gui_is($timer, 'TTimer') ){
			Timer::removeData( $timer );
			Timer::setEnabled( $timer, false );
			
			event_set( $result, 'OnTimer', null );
			self::$free[ $timer ] = true;
		}
	}
	
	static function clearInterval($timer){
		self::clearInterval($timer);
	}
	
	static function clearTimeout($timer){
		self::clearInterval($timer);
	}
	
	static function setIntervalTime($timer, $interval){
		gui_propSet($timer, 'interval', (int)$interval );
	}
	
	static function setEnabled($timer, $value){
		gui_propSet($timer, 'enabled', $value);
	}
	
	static function getEnabled($timer){
		return gui_propGet($timer, 'enabled');
	}
	
	static function setData($timer, $name, $value){
		if ( gui_is($timer, 'TTimer') ){
			self::$data[ $timer ][ $name ] = $value;
		}
	}
	
	static function getData($timer, $name){
		if ( gui_is($timer, 'TTimer') ){
			return self::$data[ $timer ][ $name ];
		} else
			return NULL;
	}
	
	static function removeData($timer){
		unset( self::$data[ $timer ] );
	}
}

?>