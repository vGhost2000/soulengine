<?

class ev_fmProjectOptions_list
{
	function onClick($self)
	{
		$list = c($self);
		$name = $list->inText;
		$file = DOC_ROOT . 'ext/help/' . basenameNoExt($name) . '.html';
		if (file_exists($file)) {
			ic('fmProjectOptions->mod_desc')->navigate($file);
		} else {
			ic('fmProjectOptions->mod_desc')->html = t('Нет описания.');
		}
	}
}