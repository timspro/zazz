<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check();
$user_id = Authenticate::get()->getUser('user_id');

if (isset($_REQUEST['page_id']) && verifyPage($_REQUEST['page_id'], $user_id)) {
	
	if (isset($_REQUEST['project'])) {
		try {
			$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
			_Project::get()->update(array('project' => $_REQUEST['project']), 
				array('project_id' => $result[0]['project_id']));
		} catch(PDOException $e) {
			if($e->getCode() === '23000') {
				echo 'There already is a project with that name that belongs to you.';
			}
		}	
	} else if(isset($_REQUEST['default_page'])) {
		
		$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));		
		$default_page_id = _Page::get()->retrieve('page_id', array(), 
			array('page' => $_REQUEST['default_page'], 'project_id' => $result[0]['project_id']));
		if(!empty($default_page_id)) {
			$default_page_id = $default_page_id[0]['page_id'];
			$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
			_Project::get()->update(array('default_page' => $default_page_id), 
				array('project_id' => $result[0]['project_id']));		
		} else {
			echo 'There is no page with that name in this project.';
		}
		
	} else if (isset($_REQUEST['delete'])) {
		
		$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
		_Project::get()->delete(array('project_id' => $result[0]['project_id']));
		$id = _Project::get()->retrieve('project_id', array(), array(), '', 0, 1);
		_User::get()->update(array('active_project' => $id[0]['project_id']), array('user_id' => $user_id));
		
		$page_ids = _Page::get()->retrieve('page_id', array(),
			array('project_id' => $result[0]['project_id']));
		foreach ($page_ids as $page_id) {
			_Page::get()->delete(array('page_id' => $page_id['page_id']));
			_Code::get()->delete(array('page_id' => $page_id['page_id']));
			_Layout::get()->delete(array('page_id' => $_REQUEST['page_id']));
		}
		
	}
}

if (isset($_REQUEST['create'])) {
	if (empty($_REQUEST['create']) || ctype_space($_REQUEST['create'])) {
		echo 'Must have a project name.';
		return;
	}
	try {
		createProject($_REQUEST['create'], $user_id);
	} catch(PDOException $e) {
		if($e->getCode() === '23000') {
			echo 'There already is a project with that name that belongs to you.';
		}
	}
}
?>