
<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
class _User extends QueryBuilder {
public function getTable() { return "user"; }
public function getColumns() { return 
array (
  'user_id' => 
  array (
    'Field' => 'user_id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  'username' => 
  array (
    'Field' => 'username',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'UNI',
    'Default' => NULL,
    'Extra' => '',
  ),
  'password' => 
  array (
    'Field' => 'password',
    'Type' => 'varchar(60)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'last_address' => 
  array (
    'Field' => 'last_address',
    'Type' => 'varchar(25)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'token' => 
  array (
    'Field' => 'token',
    'Type' => 'varchar(60)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'first_name' => 
  array (
    'Field' => 'first_name',
    'Type' => 'varchar(60)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'last_name' => 
  array (
    'Field' => 'last_name',
    'Type' => 'varchar(60)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'last_login' => 
  array (
    'Field' => 'last_login',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => '',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => '',
  ),
  'login_error_count' => 
  array (
    'Field' => 'login_error_count',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'active' => 
  array (
    'Field' => 'active',
    'Type' => 'bit(1)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => 'b\'0\'',
    'Extra' => '',
  ),
  'active_project' => 
  array (
    'Field' => 'active_project',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
); 
}
}
?>