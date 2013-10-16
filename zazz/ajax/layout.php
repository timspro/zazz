<?php

require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);

if (isset($_REQUEST['layout']) && isset($_REQUEST['page_id']) && 
	verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'))) {
	_Layout::get()->update($_REQUEST, array('page_id' => $_REQUEST['page_id']));
}

?>
