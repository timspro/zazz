
<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
class _Layout extends QueryBuilder {
public function getTable() { return "layout"; }
public function getColumns() { return 
array (
  'page_id' => 
  array (
    'Field' => 'page_id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'layout' => 
  array (
    'Field' => 'layout',
    'Type' => 'text',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
}
?>