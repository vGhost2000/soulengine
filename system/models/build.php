<?

function php_strip_whitespace_ex($source){
        $tokens = token_get_all($source);
        // Init
        $source = '';
        $was_ws = false;
        // Process
        foreach ($tokens as $token) {
            if (is_string($token)) {
                // Single character tokens
                $source .= $token;
            } else {
                list($id, $text) = $token;
                switch ($id) {
                    // Skip all comments
                    case T_COMMENT:
                    case T_ML_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    // Remove whitespace
                    case T_WHITESPACE:
                        // We don't want more than one whitespace in a row replaced
                        if ($was_ws !== true) {
                            $source .= ' ';
                        }
                        $was_ws = true;
                        break;
                    default:
                        $was_ws = false;
                        $source .= $text;
                        break;
                }
            }
        }
        
        return $source;
}


class DS_Build {

}

class CodeBuilder
{
	private $form = '';
	private $code = '';
	private $objects;
	private $events = array();

	public function __construct($cform)
	{
		$this->form = $cform;
		$this->code = '<' . "?php\n CLASS TForm" . $cform . " EXTENDS vGObjectForm\n{\n";
		$this->code .= 'static $SelfObj = False;' . "\n";
		$this->code .= '
			Protected	$___MustBeObj	= Array(
									"SCR"				=> Array("SCREEN"),
									"APP"				=> Array("APPLICATION"),
									"SCREEN"			=> Array("SCREEN"),
									"APPLICATION"		=> Array("APPLICATION"),
			);

			Public Function __construct($Name)
			{
				parent::__construct($Name);
				self::$SelfObj = $this;
				/* <OnCreateFormCode ---JhdYndkldfKkfkjkjk000dL3243ms;d-> */
			}
		';

		$this->objects = array();
		$objects       = myProject::getFormsObjects();
		foreach ($objects as $form => $data) {
			if (strtolower($form) == $this->form) {
				foreach ($data as $info) {
					$this->objects[$info['NAME']] = $info['CLASS'];
				}
				$this->objects[$this->form] = 'TForm';
				$this->objects['--fmedit'] = 'TForm';
				break;
			}
		}
	}


	public function addEvent($component, $action, $code)
	{
		if ($component == '--fmedit') {
			$component = $this->form;
		}

		if (empty($this->objects)) {
			throw new Exception(t('CodeBuilder не обнаружил компонентов для формы ') . $this->form . t(' но был вызван метод добавления события'));
		}
		if (empty($this->objects[$component])) {
			throw new Exception(t('CodeBuilder не обнаружил компонента ') . $component . t(' но был вызван метод добавления события ему'));
		}

		if ($component == $this->form && $action == 'oncreate') {
			$this->code = str_replace('/* <OnCreateFormCode ---JhdYndkldfKkfkjkjk000dL3243ms;d-> */', $code, $this->code);
			return true;
		}

		if (empty($this->events[$action])) {
			$this->events[$action] = array();
		}
		$this->events[$action][] = $component;

		$S = $R = array();
		if (preg_match_all('#[^\w]c\s*\(\s*(?:\'|"|\$)([a-z0-9_>-]+)(?:\'|"|)\s*\)#si', ' ' . $code, $m)) {
			foreach ($m[0] as $key => $search) {
				$pattern = $m[1][$key];
				$cpos = strpos($pattern, '->');
				if ($cpos) {
					$pattern = '$GLOBALS[\'' . strtolower(substr($pattern, 0, $cpos)) . '\']' . substr($pattern, $cpos);
				} else {
					$pattern = '$this->' . $pattern;
				}
				if ($search[0] != 'c' && $search[0] != 'C') {
					$pattern = $search[0] . $pattern;
				}
				$S[] = $search;
				$R[] = $pattern;
			}
			$code = str_replace($S, $R, $code);
		}

		$this->code .= '
			Public Function ' . $action . $component . '(' . DSApi::getEventParams($action, $this->objects[$component]) . ')
			{
				global $APPLICATION, $SCREEN, $_c, $progDir, $_PARAMS, $argv;
				' . $code . '
			}
		';
		return true;
	}


	public function getEncodedClassCode()
	{
		return self::compileByteCode($this->getClassCode());
	}


	public static function compileByteCode($code)
	{
		$tmp = TEMP_DIR . 'ds3_proj_' . rand(1,999999) . microtime(1) . md5($code) . '.php';
		file_put_contents($tmp, $code);
		$code = self::compileByteFile($tmp);
		unlink($tmp);
		return $code;
	}


	public static function compileByteFile($file)
	{
		//unset($code);
		$fh = fopen('php://memory', 'w+');
		bcompiler_write_header($fh);
		bcompiler_write_file($fh, $file);
		bcompiler_write_footer($fh);
		fseek($fh, 0);
		$code = fread($fh, 99999999);
		fclose($fh);

		return $code;
	}


	public function getClassCode()
	{
		$this->code .= 'protected $___Events = array(';
		foreach ($this->events as $event => $components) {
			$this->code .= "array('" . implode("', '", $components) . "'),\n";
		}
		$this->code .= ");\n}\n";

		if (vGDEBUG) {
			global $projectFile;
			file_put_contents(dirname($projectFile) . '/' . $this->form . '-' . md5($this->code) . '.php');
		}
		return $this->code;
	}
}
