<?php
require_once dirname(__FILE__) . '/../includes/standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';

function zazzGetParameters($string) {
	$quote = false;
	$quote_char = '';
	$current = '';
	$parameter = false;
	$params = array();
	for ($i = 0; $i < strlen($string); $i++) {
		$char = $string[$i];
		if ($char === "'" || $char === '"') {
			$quote = !$quote;
			$quote_char = $char;
			if ($quote_char === '"') {
				
			}
		} else if (!$quote) {
			if ($char === ":") {
				$parameter = true;
			} else if ($parameter) {
				if (!ctype_space($char)) {
					$current .= $char;
				} else {
					$params[] = $current;
					$current = '';
				}
			}
		}
	}
}

if (isset($_REQUEST['type']) && isset($_REQUEST['zazz_id']) && 
	isset($_REQUEST['zazz_order']) && isset($_REQUEST['page_id'])) {
	if(isset($_REQUEST['delete'])) {
		_Code::get()->delete($_REQUEST);
	} else if(isset($_REQUEST['code'])) {
		_Code::get()->create($_REQUEST, true);
	}
	$_zazz_results = _Code::get()->retrieve(array('code', 'type'), array(),
		array('zazz_id' => $_REQUEST['zazz_id'], 'page_id' => $_REQUEST['page_id']), 'zazz_order');
	foreach ($_zazz_results as $_zazz_result) {
		switch ($_zazz_result['type']) {
			case 'css':
				break;
			case 'html':
				echo $_zazz_result['code'];
				break;
			case 'js':
				break;
			case 'mysql':
				$q = Database::get()->PDO()->prepare($_zazz_result['code']);
				$params = zazzGetParameters($_zazz_result['code']);
				foreach ($params as $param) {
					$q->bindValue(':' . $param, $$param);
				}
				$r = $q->execute();
				echo $r;
				break;
			case 'php':
				eval($_zazz_result['code']);
				break;
		}
	}
}
?>
