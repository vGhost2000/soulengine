<?

$result = array();

$result['GROUP']   = 'system';
$result['CLASS']   = basenameNoExt(__FILE__);
$result['CAPTION'] = t('TDropFiles_Caption');
$result['SORT']    = 810;
$result['NAME']    = 'dropFiles';

$result['PROPS'] = array('enabled' => true);

return $result;