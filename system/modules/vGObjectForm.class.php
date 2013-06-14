<?php

CLASS vGObjectForm EXTENDS vGFormLinks
{
	Protected	$___Events			= Array();
	Protected	$___MusBeObj		= Array();
	
	Public Function __construct($name)
	{
		parent::__construct($name);
		
		ForEach($this->___Events AS $Event => $Elements)
		{
			ForEach($Elements AS $Element)
			{
				$This = $this;
				
				If(!Is_Array($Element))
				{
					// точное соответствие метода событию генерируемое по маске EventNameComponentName
					// 'onClick'			=> Array('DoBan', 'ChangeGroup', 'Close'),
					//set_event($this->$Element->self, $Event, '$GLOBALS["' . $name . '"]->' . $Event . ($Element == 'self' ? $name : $Element)); // DS 2.0
					$vGEvent	= $Event . ($Element == 'self' ? $name : $Element);
					$Component	= $this->$Element->self;
				}
				ElseIf(Count($Element) == 2)
				{
					// название вызываемого метода задаётся пользователем
					// 'OnKeyUp'			=> Array(Array('Grid', 'onChangeGrid')),
					//set_event($this->{$Element[0]}->self, $Event, '$GLOBALS["' . $name . '"]->' . $Element[1]);  // DS 2.0
					$vGEvent	= $Element[1];
					$Component	= $this->{$Element[0]}->self;
				}
				Else
				{
					//имя объекта чеё метод использовать в глобалсах, задаётся пользователем
					//set_event($this->{$Element[0]}->self, $Event, '$GLOBALS["' . $Element[1] . '"]->' . $Element[2]);  // DS 2.0
					$This		= $GLOBALS[$Element[1]];
					$vGEvent	= $Element[2];
					$Component	= $this->{$Element[0]}->self;
				}
				event_set($Component, $Event,
					function($self, &$V1 = NULL, &$V2 = NULL, &$V3 = NULL, &$V4 = NULL, &$V5 = NULL, &$V6 = NULL, &$V7 = NULL, &$V8 = NULL) use ($This, $vGEvent)
						{ $This->$vGEvent($self, $V1, $V2, $V3, $V4, $V5, $V6, $V7, $V8);} 
/*
					function() use ($This, $vGEvent)
					{
						Call_User_Func(Array($This, $vGEvent), Func_Get_Args());
					}
//*/
				);
			}
		}
	}
//					function($self, &$V1 = NULL, &$V2 = NULL, &$V3 = NULL, &$V4 = NULL, &$V5 = NULL, &$V6 = NULL, &$V7 = NULL, &$V8 = NULL) use ($This, $vGEvent)
//						{ $This->$vGEvent($self, $V1, $V2, $V3, $V4, $V5, $V6, $V7, $V8);} 
	
	//########################################################################################################################################################################################################
	
	Function __get($name)
	{
		If(IsSet($this->___MustBeObj[$name]))
		{
			$tmp = False;
			ForEach($this->___MustBeObj[$name] AS $el)
			{
				If($tmp)
				{
					$tmp = IsSet($tmp->$el) ? $tmp->$el : False;
				}
				Else
				{
					$tmp = IsSet($GLOBALS[$el]) ? $GLOBALS[$el] : False;
				}
				If(!$tmp)
				{
					Return NULL;
				}
			}
			$this->$name = $tmp;
			Return $tmp;
		}
		
		Return parent::__get($name);
	}
	
	//########################################################################################################################################################################################################
	
	Function __isset($name)
	{
		If(IsSet($this->___MusBeObj[$name]))
		{
			$tmp = $this->$name;
			Return $tmp === NULL ? False : True;
		}
		Return parent::__isset($name);
	}
}


?>