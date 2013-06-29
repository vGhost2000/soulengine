<?php

CLASS vGFormLinks
{
	Protected	$___ThisFormName;
	Protected	$___MustBeObj		= Array();
	Protected	$___Events			= Array();
	
	Public Function __construct($name)
	{
		$this->___ThisFormName = $name;
	}
	
	//########################################################################################################################################################################################################
	
	Function __get($name)
	{
		$tmp = c($this->___ThisFormName . ($name == 'self' ? '' : '->' . $name));
		If($tmp)
		{
			// браузер в DS не сразу становится браузером, где то в DS происходит подмена класса по этому сразу делать на него вечную ссылку низзя
			If(Get_Class($tmp) == 'TWebBrowserEx' && !Method_Exists($tmp, 'navigate'))
			{
				Return $tmp;
			}
			$GLOBALS[$this->___ThisFormName]->$name = $tmp;
		}
		Return $tmp;
	}
	
	//########################################################################################################################################################################################################
	
	Function __isset($name)
	{
		$tmp = c($this->___ThisFormName . ($name == 'self' ? '' : '->' . $name));
		If($tmp)
		{
			// браузер в DS не сразу становится браузером, где то в DS происходит подмена класса по этому сразу делать на него вечную ссылку низзя
			If(Get_Class($tmp) == 'TWebBrowserEx' && !Method_Exists($tmp, 'navigate'))
			{
				Return True;
			}
			$GLOBALS[$this->___ThisFormName]->$name = $tmp;
			Return True;
		}
		Return False;
	}
}


?>