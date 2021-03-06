<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
/* DO NOT EDIT THIS FILE */
class _Project extends QueryBuilder {
public function getTable() { return "project"; }
public function getColumns() { return 
array (
  'project_id' => 
  array (
    'Field' => 'project_id',
    'Type' => 'int(10) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  'project' => 
  array (
    'Field' => 'project',
    'Type' => 'varchar(50)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'user_id' => 
  array (
    'Field' => 'user_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'default_page' => 
  array (
    'Field' => 'default_page',
    'Type' => 'int(10) unsigned',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'project_start' => 
  array (
    'Field' => 'project_start',
    'Type' => 'int(10) unsigned',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'project_end' => 
  array (
    'Field' => 'project_end',
    'Type' => 'int(11) unsigned',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
public function getIndexes() { return 
array (
  'PRIMARY' => 
  array (
    0 => 'project_id',
    1 => 'project',
  ),
  'PROJECT' => 
  array (
    0 => 'project',
    1 => 'user_id',
  ),
);
}
}
?>