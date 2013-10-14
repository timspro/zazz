<?php

/*
 * This file should be included at the start of all files.
 */

require_once dirname(__FILE__) . '/classes/Database.php';
require_once dirname(__FILE__) . '/classes/Logger.php';

define('PREFIX', '');
define('DEVELOPER', true);
define('DATABASENAME', 'zazz');

//Start the logger (necessary due to lazy construction).
if(!defined('CONFIGURE')) {
	Database::get(array('localhost', 'root', '', DATABASENAME));
	Logger::get();
} else {
	//Set up the database with the appropiate parameters.
	Database::get(array('localhost', 'root', '', ''));
}

require_once dirname(__FILE__) . '/functions.php';

?>
