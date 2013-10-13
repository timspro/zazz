
<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
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
    'Key' => 'UNI',
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
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
}
?>