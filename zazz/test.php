<?php

require_once dirname(__FILE__) . '/includes/standard/initialize.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';

$results = _Layout::get()->retrieve(array());

print_r($results);
?>

