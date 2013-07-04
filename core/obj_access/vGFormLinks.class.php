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

	protected function getComponent($name)
	{
		if (substr($name, 0, 9) == '___uUutf_') {
			return uc($this->___ThisFormName . ($name == 'self' ? '' : '->' . substr($name, 9)));
		} else {
			return c($this->___ThisFormName . ($name == 'self' ? '' : '->' . $name));
		}
	}

	//########################################################################################################################################################################################################

	Function __get($name)
	{
		$tmp = $this->getComponent($name);
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
		$tmp = $this->getComponent($name);
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