<?

class mySyntaxCheck {
    
    static $errors;
    static $noerrors = array();
	public static $code_to_check;
    
    function showErrors(){
        
        $list = c('fmMain->debugList');
        $list->onClick = 'mySyntaxCheck::clickError';
        $list->onDblClick = 'mySyntaxCheck::dblClickError';
        $list->text = '';
        foreach ((array)self::$errors as $err){
            
            $obj_name = $err['form'];
            if ($err['obj'])
                $obj_name .= '->'.$err['obj'];
                
            if ($obj_name=='___scripts'){
                $obj_name = t('Скрипт проекта');
                $err['event'] = $err['event'];
            }
                
            $line = '['.t('Error').']: {'.$obj_name.', '.t($err['event']).'}  '.$err['msg'].' '.t('on line').' '.$err['line'];
            $list->text .= $line . _BR_;
        }
        
        $list->itemIndex = 0;
        if (count(self::$errors)>0)
        message_beep(MB_ICONERROR);
    }


	/*
	*	Метод проверяет ошибки синтаксиса в исходном коде и результат заносит в массив self::$errors
	*
	*	@param  string  проверяемуй php код
	*	@param  string  имя проверяемого события, путь и имя файла если проверяется файл
	*	@param  string  имя формы на которой находится комопнент, "___scripts" в случае если проверяется файл скрипта из папки scripts
	*	@param  string  имя компонента, события которого проверять, false в случае если это файл.
	*
	*	@return void
	*/
	private static function doCheckSintaxNew($code, $event, $form, $component)
	{
		self::$code_to_check = $code;
		check_code_for_errors();

		if (!self::$code_to_check) {
			return;
		}

		// parse error|2|8
		$buf = explode('|', self::$code_to_check);
		self::$errors[] = array(
			'msg'   => $buf[0],
			'type'  => (int)$buf[1],
			'line'  => (int)$buf[2],
			'event' => $event,
			'form'  => $form,
			'obj'   => $component
		);
	}


	/*
	*	Метод проверяет ошибки синтаксиса файла проекта из scripts или события, если передан второй параметр
	*
	*	@param  string  имя файла или формы у которой проверять события
	*	@param  string  имя компонента, события которого проверять
	*
	*	@return void
	*/
	private static function checkSintaxNew($object, $component = false)
	{
		if (!$component) {
			$code = trim(file_get_contents($object));
			if (preg_match('#^\<\?(?:php)?(.+)\?\>$#si', $code, $m)) {
				$code = $m[1];
			}

			self::doCheckSintaxNew($code, $object, '___scripts', false);
			return;
		}

		if ($object == $event) {
			$eventList = eventEngine::getEvents($object, '--fmedit');
		} else {
			$eventList = eventEngine::getEvents($object, $component);
		}

		foreach ($eventList as $event => $code) {
			self::doCheckSintaxNew($code, $event, $object, $component);
		}
	}


	/*
	*	Метод запускает проверку кода через отдельное событие delphi (runkit_lint сцуко генерит fatal error /!\текст/!\ в "консоль SAPI",
	*	а php4delhi его как fatal error воспринимает и прерывает дальнейшее исполнение скрипта)
	*	Данный говонохак позволяет выполнить код как бы в отдельном "событии" и перехватить fatal error от php4delphi
	*
	*	@return void
	*/
	public static function doCheckCodeErrors()
	{
		runkit_lint(self::$code_to_check);
		self::$code_to_check = false;
	}


	/*
	*	Метод проверяет файлы и события проекта на синтаксические ошибки в коде
	*
	*	@return boolean результат успешной проверки на безошибочность
	*/
	function checkProject()
	{
		global $projectFile;

		self::$errors = array();
		$list  = myProject::getFormsObjects();

		// проверяем синтаксис событий
		foreach ($list as $form => $objs) {
			self::checkSintaxNew($form, $form);
			foreach ($objs as $obj) {
				self::checkSintaxNew($form, $obj['NAME']);
			}
		}

		// проверяем синтаксис файлов в папке scripts
		$scripts = findFilesV2( dirname($projectFile) . '/scripts/', 'php|inc', true, true);
		foreach ($scripts as $file){
			self::checkSintaxNew($file);
		}

		// file_put_contents($dir.'noerror.log', implode("\n", self::$noerrors));       НАХРЕНА ???

		if (count(self::$errors) > 0) {
			return false;
		} else {
			return true;
		}
	}


    static function clickError($self){
        
        $index = c($self)->itemIndex;
        if ($index==-1) return;
        
        global $_FORMS, $formSelected, $fmEdit, $_sc, $myEvents;
        
        $error = self::$errors[$index];
        if (!$error) return;
        
        if ($error['form']=='___scripts'){
            
            return;
        }
        
        
        if (strtolower($_FORMS[$formSelected])!=strtolower($error['form'])){
                
                eventEngine::setForm($error['form']);
                myUtils::saveForm();
                myUtils::loadForm($error['form']);
        }
        
        if (!$error['obj']){
            $_sc->clearTargets();
            myDesign::formProps();
        } else { 
            
            myDesign::inspectElement( $fmEdit->findComponent($error['obj']) );    
        }
        
        if (!$error['event']) $error['event'] = 'OnExecute';
        c('fmMain->eventList')->items->selected = t(strtolower($error['event']));
    }
    
    static function dblClickError($self){
        
        $index = c($self)->itemIndex;
        if ($index==-1) return;
        $error = self::$errors[$index];
        
        self::clickError($self);
        
        if ($error['form']=='___scripts'){
            
            global $projectFile;
            
            run($error['event']);
            return;
        }
        
        myEvents::editorShow($error['line']);
    }
}