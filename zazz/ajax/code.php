<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);

if (isset($_REQUEST['type']) && isset($_REQUEST['zazz_id']) && isset($_REQUEST['zazz_order'])
	&& isset($_REQUEST['page_id'])) {
	$check = verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'));
	if ($check) {
		$run = true;
		$updatePageID = $_REQUEST['page_id'];
		if ($_REQUEST['zazz_id'] === 'begin-project') {
			$run = false;
			$updatePageID = $check[0]['project_start'];
		} else if ($_REQUEST['zazz_id'] === 'end-project') {
			$run = false;
			$updatePageID = $check[0]['project_end'];
		} else if ($_REQUEST['zazz_id'] === 'begin-web-page' || $_REQUEST['zazz_id'] === 'end-web-page') {
			$run = false;
		}
		if (isset($_REQUEST['delete'])) {
			_Code::get()->delete($_REQUEST);
		} else if (isset($_REQUEST['code'])) {
			if ($_REQUEST['insert'] === 'true') {
				_Code::get()->create(array('code' => $_REQUEST['code'],
				'zazz_id' => $_REQUEST['zazz_id'], 'page_id' => $updatePageID, 'type' =>
				$_REQUEST['type'], 'zazz_order' => $_REQUEST['zazz_order']));
			} else {
				_Code::get()->update(array('code' => $_REQUEST['code']),
					array('zazz_id' => $_REQUEST['zazz_id'], 'page_id' => $updatePageID, 'type' =>
					$_REQUEST['type'], 'zazz_order' => $_REQUEST['zazz_order']));
			}
		}

		if ($run) {
			processCode($check[0]['project_start'], $check[0]['project_end'], $_REQUEST['page_id'],
				$_REQUEST['zazz_id']);
		} else {
			echo getComputedLayout($check[0]['project_start'], $check[0]['project_end'], $_REQUEST['page_id']);
		}
	}
}
?>
