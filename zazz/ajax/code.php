<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);
$user_id = Authenticate::get()->getUser('user_id');

$page_id = intval($_REQUEST['page_id']);

if (isset($_REQUEST['type']) && isset($_REQUEST['zazz_id']) && isset($_REQUEST['zazz_order'])
	&& isset($page_id)) {
	$check = verifyPage($page_id, $user_id);
	if ($check) {
		$run = true;
		$updatePageID = $page_id;
		if ($_REQUEST['zazz_id'] === 'begin-project') {
			$run = false;
			$updatePageID = $check[0]['project_start'];
		} else if ($_REQUEST['zazz_id'] === 'end-project') {
			$run = false;
			$updatePageID = $check[0]['project_end'];
		} else if ($_REQUEST['zazz_id'] === 'begin-web-page' || $_REQUEST['zazz_id'] === 'end-web-page') {
			$run = false;
		}
		if (isset($_REQUEST['deleted'])) {
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
		
		if (isset($_REQUEST['moveTo'])) {
			$query = Database::get()->PDO()->prepare('UPDATE code SET zazz_order = zazz_order + 1 WHERE ' .
				'page_id = :page_id AND zazz_id = :zazz_id AND zazz_order >= ' . intval($_REQUEST['moveTo']) .
				' ORDER BY zazz_order DESC; UPDATE code SET zazz_order = ' . intval($_REQUEST['moveTo']) . 
				' WHERE page_id = :page_id AND zazz_id = :zazz_id AND zazz_order = :zazz_order');
			$query->bindValue(':page_id', $page_id);
			$query->bindValue(':zazz_id', $_REQUEST['zazz_id']);
			$zazz_order = intval($_REQUEST['zazz_order']);
			if($zazz_order >= intval($_REQUEST['moveTo'])) {
				$zazz_order++;
			}
			$query->bindValue(':zazz_order', $zazz_order);
			$query->execute();			
			$query->closeCursor();
		}
		
		if (isset($_REQUEST['unlink'])) {
			$query = Database::get()->PDO()->prepare('INSERT INTO code (zazz_id, page_id, type, code, ' .
				'zazz_order) SELECT zazz_id, ' . $page_id . ', type, code, zazz_order FROM code WHERE ' .
				'page_id = ' . intval($check[0]['template']) . ' AND zazz_id = :zazz_id');
			$query->bindValue(':zazz_id', $_REQUEST['zazz_id']);
			$query->execute();			
		}

		$basedir = dirname(__FILE__) . '/../view/' . $user_id . '/' . $check[0]['project'] . '/';
		if ($run) {
			processCode($check[0]['project_start'], $check[0]['project_end'], $page_id,
				$_REQUEST['zazz_id'], $basedir, $check[0]['template']);
		} else {
			echo getComputedLayout($check[0]['project_start'], $check[0]['project_end'], 
				$page_id, $basedir, $check[0]['template']);
		}
	}
}
?>
