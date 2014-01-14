<?php

function generateRandomString() {
	$strong = false;
	return substr(str_replace('/', '_', str_replace('+', '$', base64_encode(openssl_random_pseudo_bytes(16, $strong)))), 0, 16);
}

$deployPassword = generateRandomString();
$globalPassword = generateRandomString();
$databasePassword = '';
$deletePassword = generateRandomString();

session_start();
session_destroy();

function myErrorHandler($errno, $errstr, $errfile, $errline) {
	echo $errstr . '<br>';
	exit();
}

set_error_handler("myErrorHandler");

if (!extension_loaded('zip')) {
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

if (isset($_GET['delete']) && (empty($deletePassword) || $_GET['delete'] === $deletePassword)) {
	require_once dirname(__FILE__) . '/includes/standard/delete.php';

	//Comes from custom/functions.php.
	function deleteFilesIn($folder, $exclude = array()) {
		if (file_exists($folder)) {
			foreach (new DirectoryIterator($folder) as $item) {
				$filename = $item->getFilename();
				if (!$item->isDot() && !in_array($filename, $exclude)) {
					if ($item->isDir()) {
						deleteFilesIn($folder . $filename . '/');
						rmdir($folder . $filename);
					} else {
						unlink($folder . $filename);
					}
				}
			}
		}
	}

	deleteFilesIn(dirname(__FILE__) . '/view/');
}
require_once dirname(__FILE__) . '/includes/standard/configure.php';

//$filename = dirname(__FILE__);
//copy($filename . '/includes/functions.min.js', $filename . '/js/functions.js');
//copy($filename . '/includes/jquery-1.10.2.min.js', $filename . '/js/jquery-1.10.2.js');
//copy($filename . '/includes/style.css', $filename . '/css/style.css');

echo 'Configuration completed. <br>';

copy(__FILE__, dirname(__FILE__) . '/includes/configure.php');
unlink(__FILE__);
?>
