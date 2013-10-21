<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);
$user_id = Authenticate::get()->getUser('user_id');

function printFiles($user_id, $project, $page_id) {
	$filename = dirname(__FILE__) . '/../view/' . $user_id . '/' . $project . '/css/resources/';
	$last = 'You have no resources uploaded.</div>';
	$first = true;
	if(file_exists($filename)) {
		foreach (new DirectoryIterator($filename) as $item) {
			$name = $item->getFilename();
			if (!$item->isDot()) {
				if ($first) {
					echo '<table><tr><td></td><td>All Resources:</td></tr>';
					$last = '</table></div><input type="hidden" id="-zazz-page-id" value="' . $page_id . '"/>
<script src="/zazz/js/jquery-1.10.2.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
		$(".-zazz-modal-files").click(function(e){
			var $target = $(e.target);
			if($target.hasClass("-zazz-delete-resource")) {
				$.post("/zazz/ajax/project.php", {
					page_id: $("#-zazz-page-id").val(),
					delete_upload: $target.parent().next().children(":first").html()
				}, function(){
					$target.parent().parent().hide();
				});
			}
		});
})
</script>';
					$first = false;
				}
				echo '<tr><td><img class="-zazz-delete-resource" src="/zazz/css/images/x.png"/></td>' . 
					'<td><a href="/zazz/view/' 
					. $project . '/css/resources/' . $name . '">' .	$name . '</a></td></tr>';
			}
		}
	}
	echo $last;
}

if (isset($_REQUEST['page_id']) && verifyPage($_REQUEST['page_id'], $user_id)) {

	if (isset($_REQUEST['delete_upload'])) {
		$result = _Project::get()->retrieve('project', new Join('project_id', _Page::get()), 
			array('page_id' => $_REQUEST['page_id']));
		$project = $result[0]['project'];
		unlink(dirname(__FILE__) . '/../view/' . $user_id . '/' . $project . '/css/resources/' . 
			$_REQUEST['delete_upload']);
		return;
	}
	
	if (isset($_REQUEST['files'])) {
		include_once dirname(__FILE__) . '/../includes/custom/header.php';
		echo '<base target="_parent" /><div class="-zazz-modal-files">';
		$result = _Project::get()->retrieve('project', new Join('project_id', _Page::get()), 
			array('page_id' => $_REQUEST['page_id']));
		$project = $result[0]['project'];
		printFiles($user_id, $project, $_REQUEST['page_id']);
		return;
	}

	if (isset($_REQUEST['upload_name'])) {
		include_once dirname(__FILE__) . '/../includes/custom/header.php';
		echo '<base target="_parent" /><div class="-zazz-modal-files">';
		$result = _Project::get()->retrieve('project', new Join('project_id', _Page::get()), 
			array('page_id' => $_REQUEST['page_id']));
		if(empty($result)) {
			echo "Could not find project name.</div>";
			return;
		}
		$project = $result[0]['project'];
		$filename = dirname(__FILE__) . '/../view/' . $user_id . '/' . $project . '/css/resources/';
		$name = $_REQUEST['upload_name'];
		if (strrpos($name, '..') !== false) {
			echo 'You may not use ".." in the filename.<br /><br />';
			printFiles($user_id, $project, $_REQUEST['page_id']);
			return;
		}
		if (!file_exists($filename)) {
			mkdir($filename, 0777, true);
		} else if (file_exists($filename . $name)) {
			echo 'There is already a file with that name. Delete it first if you want to overwrite it.<br /><br />';
			printFiles($user_id, $project, $_REQUEST['page_id']);
			return;
		}
		move_uploaded_file($_FILES["upload"]["tmp_name"], $filename . $name);
		printFiles($user_id, $project, $_REQUEST['page_id']);
		return;
	}

	if (isset($_REQUEST['project'])) {
		try {
			$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
			_Project::get()->update(array('project' => $_REQUEST['project']),
				array('project_id' => $result[0]['project_id']));
		} catch (PDOException $e) {
			if ($e->getCode() === '23000') {
				echo 'There already is a project with that name that belongs to you.';
			}
		}
	} else if (isset($_REQUEST['default_page'])) {

		$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
		$default_page_id = _Page::get()->retrieve('page_id', array(),
			array('page' => $_REQUEST['default_page'], 'project_id' => $result[0]['project_id']));
		if (!empty($default_page_id)) {
			$default_page_id = $default_page_id[0]['page_id'];
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
	} catch (PDOException $e) {
		if ($e->getCode() === '23000') {
			echo 'There already is a project with that name that belongs to you.';
		}
	}
}
?>