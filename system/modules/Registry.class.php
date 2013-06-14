<?php

CLASS Registry
{
	Static Protected $Counter	= 0;
	Static Protected $store		= Array();
	
	Protected Function __construct() {}
	Protected Function __clone() {}
	 
	Static Protected Function Exists($name) 
	{
		Return IsSet(self::$store[$name]);
	}
	 
	Static Public Function Get($name)
	{
		Return (IsSet(self::$store[$name])) ? self::$store[$name] : NULL;
	}
	
	Static Public Function iSet($name, $obj) 
	{
		self::$Counter++;
		self::$store[$name . '_' . self::$Counter] = $obj;
		Return $name . '_' . self::$Counter;
	}
	
	Static Public Function Set($name, $obj) 
	{
		self::$store[$name] = $obj;
		Return $obj;
	}
}


?>