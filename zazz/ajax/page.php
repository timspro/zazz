<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);
if (!verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'))) {
	return;
}

if (isset($_REQUEST['page']) && isset($_REQUEST['page_id'])) {
	try {
		_Page::get()->update(array('page' => $_REQUEST['page']), array('page_id' => $_REQUEST['page_id']));
	} catch (PDOException $e) {
		if ($e->getCode() === '23000') {
			echo 'There already is a page with that name in this project.';
		}
	}
	return;
}

if (isset($_REQUEST['create']) && isset($_REQUEST['page_id'])) {
	if (empty($_REQUEST['create']) || ctype_space($_REQUEST['create'])) {
		echo 'Must have a page name.';
		return;
	}
	try {
		$project_id = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
		$project_id = $project_id[0]['project_id'];		
		
		createPage($_REQUEST['create'], $project_id);
	} catch (PDOException $e) {
		if ($e->getCode() === '23000') {
			echo 'There already is a page with that name in this project.';
		}
	}
}

if (isset($_REQUEST['delete']) && isset($_REQUEST['page_id'])) {
	_Page::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Code::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Layout::get()->delete(array('page_id' => $_REQUEST['page_id']));
}
?>