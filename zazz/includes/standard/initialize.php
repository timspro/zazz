<?php

/*
 * This file should be included at the start of all files.
 */

require_once dirname(__FILE__) . '/classes/Database.php';
require_once dirname(__FILE__) . '/classes/Logger.php';

define('PREFIX', '');
define('DEVELOPER', true);

//Set up the database with the appropiate parameters.
Database::get(array('localhost', 'root', '', 'zazz'));
//Start the logger (necessary due to lazy construction).
if(!defined('CONFIGURE')) {
	Logger::get();
}

require_once dirname(__FILE__) . '/functions.php';

?>
