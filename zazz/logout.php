<?php

include_once dirname(__FILE__) . '/includes/standard/initialize.php';
$user = Authenticate::get();
$user->logout();
$user->check();

?>
