<?
/*
  
  SoulEngine DFM Forms reader & writer
  
  2009 ver 0.5
  
  Dim-S Software (c) 2009
		
		functions:
		createForm, createFormWithEvents, saveFormAsDfm
		object_resource2text, object_text2resource
		
  Библиотека для загрузки и сохранения форм из dfm файлов...
  
  
*/

// создание формы на основе внешнего dfm файла
function dfm_read($dfm_file_name, $aform = false, $str = false, $form_name = false, $is_runtime = false)
{
	if ($dfm_file_name) {
		checkFileV2($dfm_file_name);
	}

	if (!$aform) {
		$form = new TForm($GLOBALS['APPLICATION']);
	} else {
		$form = $aform;
		$form->positionEx = $form->position;
	}

	if (!$str) {
		$str = file_get_contents($dfm_file_name);
	}
	if (empty($str)) {
		throw new Exception('ERROR: dfm_read failed get dfm structure!');
	}

	gui_readStr($form->self, $str);

	if ($form_name) {
		$form->name = $form_name;
	}

	$components = $form->componentList;

	for ($i=0;$i<count($components);$i++){
		
		$el =& $components[$i];
		
		if (!$GLOBALS['APP_DESIGN_MODE'] || $is_runtime){
			
			if (!$el->isClass(array('TEvents','TTabSheet')) && !$el->name){
				$el->free();
				continue;
			}
			
			if (method_exists($el, '__initComponentInfo')){
				$el->__initComponentInfo();
			}		
		
		} else {
			
		}
	}
		
 return $form->self;
}

// сохранение формы в dfm файл
function dfm_write($dfm_file_name, TForm $form)
{
	$dfm_file_name = replaceSr($dfm_file_name);

	$components = $form->components;
	if (is_array($components)) {
		foreach ($components as $el) {
			if (method_exists($el, '__getAddSource')){
				$el->__getAddSource();
				//$help = unserialize(base64_decode($el->getHelpKeyword()));
			}
		}
	}

	file_put_contents($dfm_file_name, gui_writeStr($form->self) );
}

// ---------------------------- // -------------------------------------------//

function createForm($file){
        return _c(dfm_read($file));
}

function saveFormAsDfm($file,$form){
	
	$form = toObject($form);
        dfm_write($file,$form);
}

function createFormWithEvents($name, $init = false){
	global $progDir;
	if (!is_file('phar://system.phar/' . $name . '.dfm')) {
		throw new Exception('createFormWithEvents: phar://system.phar/' . $name . '.dfm');
	}
	$res = createForm('phar://system.phar/' . $name . '.dfm');
	
	if (file_exists('phar://system.phar/' . $name.'.php')){

		require_once('phar://system.phar/' . $name.'.php');
		if ($init) {
			loadFormEvents($res);
		}
	}
	return $res;
}

// динамическая загрузка событий для формы...
function loadFormEvents(TForm &$form){
        
	
        $name = $form->name;
	$objs_l = $form->componentLinks;
        
        $events = array('onClick','onClose','onCloseQuery','onDblClick','onKeyUp','onKeyPress','onKeyDown',
                        'onMouseDown','onMouseUp','onMouseMove','onMouseEnter','onMouseLeave','onCanResize',
                        'onChange','onChanging','onShow','onPaint','onResize','onHide','onActivate','onDeactivate',
                        'onDestroy','onSelect','onTimer','onScroll', 'onMouseCursor','onDockDrop','onDockOver',
			'onUndock','onStartDock','onEndDock',
                        'OnDuringSizeMove','OnStartSizeMove','OnEndSizeMove','OnPopup');
        
        for ($i=0;$i<count($objs_l);$i++){
		$self = $objs_l[$i];
		$o_name = component_name($self);
		
                for ($j=0;$j<count($events);$j++){
                        $ev = $events[$j];
                        $class = 'ev' . $name . $o_name;
			
			if (!class_exists($class))
				$class = 'ev_' . $name . '_' . $o_name; 
			if (!class_exists($class))
				$class = 'ev_' . $o_name;
			
                        if (!class_exists($class)) continue;
                        if (!method_exists($class,$ev)) continue;
			
			set_event($self, $ev, $class . '::' . $ev);
                }
        }
	
	for ($j=0;$j<count($events);$j++){
                        $ev = $events[$j];
                        
			$class = 'ev' . $name;
			if (!class_exists($class))
				$class = 'ev_' . $name;
                        
                        if (!class_exists($class)) continue;
			if (!method_exists($class,$ev)) continue;
                        
                        $form->$ev = $class . '::' . $ev;
        }
}


?>