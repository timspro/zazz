<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
/* DO NOT EDIT THIS FILE */
class _Template extends QueryBuilder {
public function getTable() { return "template"; }
public function getColumns() { return 
array (
  'page_id' => 
  array (
    'Field' => 'page_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'template_id' => 
  array (
    'Field' => 'template_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
public function getIndexes() { return 
array (
  'PRIMARY' => 
  array (
    0 => 'page_id',
    1 => 'template_id',
  ),
);
}
}
?>