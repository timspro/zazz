<?php

function getLayout($page_id) {
	$result = _Layout::get()->retrieve(array('layout'), array(), array('page_id' => $page_id));
	return $result[0]['layout'];
}

function getPageID($project, $page) {
	$user_id = Authenticate::get()->getUser('user_id');
	$id = _Page::get()->retrieve('page_id', new Join('project_id', _Project::get()),
		array('project' => $project, 'page' => $page, 'user_id' => $user_id));
	if (count($id) === 1) {
		return $id[0]['page_id'];
	}
	return null;
}

function deleteFilesIn($folder) {
	foreach (new DirectoryIterator($folder) as $item) {
		if (!$item->isDot()) {
			if ($item->isDir()) {
				deleteFilesIn($folder . $item->getFilename() . '/');
				rmdir($folder . $item->getFilename());
			} else {
				unlink($folder . $item->getFilename());
			}
		}
	}
}

?>
