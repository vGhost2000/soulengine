<?php


class modifer_TFunction
{
	/*function listEvent()
	{
		return array();
	}*/

	
	public function toResultV2($form_name, $name, $info, $eventList)
	{
		return TFunction::__register($form_name, $name, $info, $eventList);
	}
}