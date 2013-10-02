
<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
class _Code extends QueryBuilder {
public function getTable() { return "code"; }
public function getColumns() { return 
array (
  'zazz_id' => 
  array (
    'Field' => 'zazz_id',
    'Type' => 'varchar(50)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'page_id' => 
  array (
    'Field' => 'page_id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'type' => 
  array (
    'Field' => 'type',
    'Type' => 'enum(\'css\',\'html\',\'mysql\',\'js\',\'php\')',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'code' => 
  array (
    'Field' => 'code',
    'Type' => 'text',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'zazz_order' => 
  array (
    'Field' => 'zazz_order',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
}
?>