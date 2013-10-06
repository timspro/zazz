<?php

require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check();

if (isset($_REQUEST['project']) && isset($_REQUEST['page_id']) && 
	verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'))) {
	$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
	_Project::get()->update($_REQUEST, array('project_id' =>$result[0]['project_id']));
}

?>