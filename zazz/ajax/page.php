<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);
$user_id = Authenticate::get()->getUser('user_id');
if (!verifyPage($_REQUEST['page_id'], $user_id)) {
	return;
}

if (isset($_REQUEST['page']) && isset($_REQUEST['page_id'])) {
	try {
		_Page::get()->update(array('page' => $_REQUEST['page'], 'visible' => $_REQUEST['visible']), 
			array('page_id' => $_REQUEST['page_id']));
	} catch (PDOException $e) {
		if ($e->getCode() === '23000') {
			echo 'There already is a page with that name in this project.';
		}
	}
	return;
}

if (isset($_REQUEST['create']) && isset($_REQUEST['page_id'])) {
	if (empty($_REQUEST['create'])) {
		echo 'Must have a page name.';
		return;
	}
	if(!ctype_alnum($_REQUEST['create'])) {
		echo 'You may only have letters and numbers in the name.';
		return;
	}
	try {
		$project_id = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
		$project_id = $project_id[0]['project_id'];		
		
		$template = '';
		if(isset($_REQUEST['template']) && !empty($_REQUEST['template'])) {
			$template = $_REQUEST['template'];
		}
		createPage($_REQUEST['create'], $project_id, $template);
	} catch (PDOException $e) {
		if ($e->getCode() === '23000') {
			echo 'There already is a page with that name in this project.';
		}
	}
}

if (isset($_REQUEST['deleted']) && isset($_REQUEST['page_id'])) {
	_Page::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Code::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Layout::get()->delete(array('page_id' => $_REQUEST['page_id']));
}
?>