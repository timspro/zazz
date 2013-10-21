<?php
function getServerName() {
	return (intval($_SERVER['SERVER_PORT']) === 80 ? $_SERVER['SERVER_NAME'] :
						$_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']);
}
	
function getHTTPS() {
	return (empty($_SERVER['HTTPS'])) ? false : true;
}

function isLocal() {
	return $_SERVER['SERVER_NAME'] === 'localhost';
}

function ifisset(&$var) {
	return (isset($var) ? $var : '');
}
?>