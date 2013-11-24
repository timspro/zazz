<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);
$user_id = Authenticate::get()->getUser('user_id');
$check = verifyPage($_REQUEST['page_id'], $user_id);
if (!$check) {
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
	if(!validateFilename($_REQUEST['create'])) {
		echo 'You may only have letters, numbers, hyphen, underscore or period in the page name.';
		return;
	}
	try {
		$project_id = $check[0]['project_id'];		
		
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
	$result = _Page::get()->retrieve('page_id', array(), array('template' => $_REQUEST['page_id']));
	if(!empty($result)) {
		echo 'You cannot delete a template if there are still pages referencing it.';
		return;
	}
	_Page::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Code::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Layout::get()->delete(array('page_id' => $_REQUEST['page_id']));
	$project_id = intval($check[0]['project_id']);
	$query = Database::get()->PDO()->prepare("SELECT page_id FROM page WHERE project_id = $project_id AND " . 
		"page NOT IN ('begin-project', 'end-project')");
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	_Project::get()->update(array('default_page' => $result[0]['page_id']), 
		array('project_id' => $project_id));
}
?>