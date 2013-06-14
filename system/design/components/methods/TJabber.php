<?

$result = array();

$result[] = array(
                  'CAPTION'=>'connect',
                  'PROP'=>'connect()',
                  'INLINE'=>'connect( void )',
                  );

$result[] = array(
                  'CAPTION'=>'disconnect',
                  'PROP'=>'disconnect()',
                  'INLINE'=>'disconnect( void )',
                  );

$result[] = array(
                  'CAPTION'=>'sendMessage',
                  'PROP'=>'sendMessage',
                  'INLINE'=>'sendMessage( string $toJid, string $content )',
                  );
				  
$result[] = array(
                  'CAPTION'=>'seStatus',
                  'PROP'=>'seStatus',
                  'INLINE'=>'sendStatus( string $status, string $show, int $priority )',
                  );

$result[] = array(
                  'CAPTION'=>'getContacts',
                  'PROP'=>'getContacts()',
                  'INLINE'=>'getContacts( void )',
                  );

$result[] = array(
                  'CAPTION'=>'setContact',
                  'PROP'=>'setContact',
                  'INLINE'=>'setContact( string $jid, string $name, array $group )',
                  );

$result[] = array(
                  'CAPTION'=>'removeContact',
                  'PROP'=>'removeContact',
                  'INLINE'=>'removeContact( string $jid )',
                  );

return $result;

?>