<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);

if (isset($_REQUEST['type']) && isset($_REQUEST['zazz_id']) && isset($_REQUEST['zazz_order'])
	&& isset($_REQUEST['page_id'])) {
	$check = verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'));
	if ($check) {
		$run = true;
		if ($_REQUEST['zazz_id'] === 'begin-project') {
			$run = false;
			$_REQUEST['page_id'] = $check[0]['project_start'];
		} else if($_REQUEST['zazz_id'] === 'end-project') {
			$run = false;
			$_REQUEST['page_id'] = $check[0]['project_end'];
		} else if($_REQUEST['zazz_id'] === 'begin-web-page' || $_REQUEST['zazz_id'] === 'end-web-page') {
			$run = false;
		}
		if (isset($_REQUEST['delete'])) {
			_Code::get()->delete($_REQUEST);
		} else if (isset($_REQUEST['code'])) {
			if ($_REQUEST['insert'] === 'true') {
				_Code::get()->create($_REQUEST);
			} else {
				_Code::get()->update(array('code' => $_REQUEST['code']),
					array('zazz_id' => $_REQUEST['zazz_id'], 'page_id' => $_REQUEST['page_id'], 'type' =>
					$_REQUEST['type'], 'zazz_order' => $_REQUEST['zazz_order']));
			}
		}

		if ($run) {
			$_ZAZZ_BLOCKS = getPageCode($check[0]['project_start'], $check[0]['project_end'],
				$_REQUEST['page_id'], $_REQUEST['zazz_id']);
			unset($_REQUEST);
			unset($_GET);
			unset($_POST);
			foreach ($_ZAZZ_BLOCKS as $_ZAZZ_BLOCK) {
				switch ($_ZAZZ_BLOCK['type']) {
					case 'css':
						break;
					case 'html':
						echo $_ZAZZ_BLOCK['code'];
						break;
					case 'js':
						break;
					case 'mysql':
						if (!empty($_ZAZZ_BLOCK['code'])) {
							$q = Database::get()->PDO()->prepare($_ZAZZ_BLOCK['code']);
							$params = GetParametersForQuery($_ZAZZ_BLOCK['code']);
							foreach ($params as $param) {
								$q->bindValue(':' . $param, $$param);
							}
							$q->execute();
							$ZAZZ_ROWS = $q->fetchAll(PDO::FETCH_ASSOC);
							unset($q);
							unset($params);
						} else {
							$ZAZZ_ROWS = array();
						}
						break;
					case 'php':
						eval($_ZAZZ_BLOCK['code']);
						break;
				}
			}
		}
	}
}
?>
