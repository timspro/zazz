<?php

$deployPassword = 'NEPOm20dkP_e3ls0elOEMlsoW';
$globalPassword = 'B9)#@Psls0DS{ksmL:EoDZspwq';
$databasePassword = '';

function myErrorHandler($errno, $errstr, $errfile, $errline) {
	echo $errstr . '<br>';
	exit();
}

set_error_handler("myErrorHandler");

if(!extension_loaded('zip')) {
	echo 'ZIP extension not installed.';
	return;
} else {
	echo 'ZIP extension is installed.';
}

if(isset($_GET['delete'])) {
	require_once dirname(__FILE__) . '/includes/standard/delete.php';
}
require_once dirname(__FILE__) . '/includes/standard/configure.php';

echo 'Completed.';

copy(__FILE__, dirname(__FILE__) . '/includes/configure.php');
unlink(__FILE__);

?>
