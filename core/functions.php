<?php

function _empty(){}
function replaceSl($s){return str_replace("\\","/",$s);}
function replaceSr($s){return str_replace("/","\\",$s);}

/*
 * Процедура опускает строковые значения массива в нижний регистр
 * 
 * @param   array   исходный массив
 * 
 * @return  array   Результирующий массив со значениями в нижнем регистре
 */
function array_lower($array)
{
	$tmp = array();
	foreach ($array as $k => $v) {
		$tmp[$k] = is_string($v) ? strtolower($v) : $v;
	}
	return $tmp;
}


function pre($obj){
	
	if ( sync(__FUNCTION__, func_get_args()) ) return;
	
	$s = print_r($obj,true);
	gui_message($s);
}

function pre2($obj){
	
	if ( sync(__FUNCTION__, func_get_args()) ) return;
	
	ob_start();
	var_dump($obj);
	$s = ob_get_contents();
	ob_end_clean();
	gui_message($s);
}

function include_lib($class,$name){
	require ENGINE_DIR . $class . '/' . $name . '.php';
}

global $progDir, $moduleDir, $engineDir;
$progDir = str_replace('\\\\','\\',$progDir);

$prs2 = basename(param_str(2));

$prs2_ext = strtolower(substr($prs2, strrpos($prs2,'.')+1, strlen($prs2)-strrpos($prs2,'.')-1));

if ($prs2_ext=='dvsexe' || $prs2_ext=='mspprexe'){
	define('DOC_ROOT2', str_replace('//','/',replaceSl($progDir)));
	$progDir = replaceSr(dirname( param_str(2) )).'\\';
} 
define('DOC_ROOT',str_replace('//','/',replaceSl($progDir)));
define('MODULE_DIR',replaceSl($moduleDir));
define('ENGINE_DIR',replaceSl($engineDir));
define('DRIVE_CHAR', $progDir[0]);

define('progDir',$progDir);
set_include_path(DOC_ROOT);

$_SERVER['DOCUMENT_ROOT'] = DOC_ROOT;
$_SERVER['MODULE_DIR'] = MODULE_DIR;
$_SERVER['ENGINE_DIR'] = ENGINE_DIR;

function fpc_ex($filename, $data, $flags = 0, $context = null)
{
	$path = dirname($filename);
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	
	return file_put_contents($filename, $data, $flags, $context);
}






?>