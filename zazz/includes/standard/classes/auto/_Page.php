
<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
class _Page extends QueryBuilder {
public function getTable() { return "page"; }
public function getColumns() { return 
array (
  'page_id' => 
  array (
    'Field' => 'page_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  'page' => 
  array (
    'Field' => 'page',
    'Type' => 'varchar(50)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'project_id' => 
  array (
    'Field' => 'project_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'background_image' => 
  array (
    'Field' => 'background_image',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
}
?>