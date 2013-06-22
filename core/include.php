<?

function _empty(){}
function replaceSl($s){return str_replace("\\","/",$s);}
function replaceSr($s){return str_replace("/","\\",$s);}


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
/* %START_MODULES% */
require_once('main/constant.php');
require_once('debug/errors.php');
require_once('debug/bytecode.php');
require_once('debug/debugclass.php');
require_once('config.php');
require_once('main/objects.php');
require_once('main/classes.php');
require_once('main/messages.php');
require_once('main/graphics.php');
require_once('main/dfmreader.php');
require_once('main/forms.php');
require_once('main/dialogs.php');
require_once('main/standart.php');
require_once('main/timing.php');
require_once('main/threading.php');
require_once('main/buttons.php');
require_once('main/additional.php');
require_once('main/menus.php');
require_once('main/imagelist.php');
require_once('main/web.php');
require_once('main/grids.php');
require_once('main/registry.php');

require_once('main/keyboard.php');
require_once('main/localization.php');
require_once('main/osapi.php');
require_once('main/utils.php');
require_once('main/skins.php');

require_once('files/file.php');
require_once('files/ini.php');
require_once('files/ini_ex.php');

require_once('design/sizecontrol.php');
require_once('design/propcomponents.php');
require_once('design/dfmparser.php');
require_once('design/synedit.php');

require_once('inits.php');

if (!class_exists('CoreBuilder')) {
	require_once('coreBuilder.php');
}


__HALT_COMPILER();

