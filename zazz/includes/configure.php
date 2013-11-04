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
	echo 'ZIP extension not installed. <br>';
	return;
} else {
	echo 'ZIP extension is installed. <br>';
}

function setNewPassword($filename, $password) {
	$contents = file_get_contents($filename);
	$token = '/*!_!_!PASSWORD!_!_!*/';
	$quoted_token = preg_quote($token, '/');
	$contents = preg_replace('/' . $quoted_token . '.*?' . $quoted_token . '/',
		$token . "'" . $password . "'" . $token, $contents, 1);
	file_put_contents($filename, $contents);
}

//Custom password stuff.
setNewPassword(dirname(__FILE__) . '/login.php', $globalPassword);
setNewPassword(dirname(__FILE__) . '/view.php', $deployPassword);
setNewPassword(dirname(__FILE__) . '/includes/standard/initialize.php', $databasePassword);

if(isset($_GET['delete'])) {
	require_once dirname(__FILE__) . '/includes/standard/delete.php';
}
require_once dirname(__FILE__) . '/includes/standard/configure.php';

if(isset($_GET['min'])) {
	$filename = dirname(__FILE__);
	rename($filename . '/js/functions.min.js', $filename . '/js/functions.js');
	rename($filename . '/js/jquery-1.10.2.min.js', $filename . '/js/jquery-1.10.2.js');
	rename($filename . '/css/style.min.css', $filename . '/css/style.css');
}

echo 'Configuration completed. <br>';

copy(__FILE__, dirname(__FILE__) . '/includes/configure.php');
unlink(__FILE__);

?>
