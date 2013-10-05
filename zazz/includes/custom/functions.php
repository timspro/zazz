<?php


require_once dirname(__FILE__) . '/../standard/classes/auto/_Layout.php';
require_once dirname(__FILE__) . '/../standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/../standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/../standard/classes/auto/_Page.php';

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

function GetParametersForQuery($string) {
	$quote = false;
	$quote_char = '';
	$current = '';
	$parameter = false;
	$params = array();
	for ($i = 0; $i < strlen($string); $i++) {
		$char = $string[$i];
		if ($char === "\\") {
			$i++;
			continue;
		}
		if ($char === "'" || $char === '"') {
			if (!$quote) {
				$quote = true;
				$quote_char = $char;
			} else if ($char === $quote_char) {
				$quote = false;
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
					$parameter = false;
				}
			}
		}
	}
	//If the parameter is the last word, then there is no space.
	if ($parameter) {
		$params[] = $current;
	}
	return $params;
}

/**
 * Makes sure that the user can modify the given page. True if OK. False otherwise.
 * @param type $page_id
 * @param type $user_id
 * @return boolean
 */
function verifyPage($page_id, $user_id) {
	$check = _Project::get()->retrieve('user_id', new Join('project_id', _Page::get()),
		array('page_id' => $page_id, 'user_id' => $user_id));
	if ($check > 0) {
		return true;
	}
	return false;
}

?>
