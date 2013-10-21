<?php
require_once dirname(__FILE__) . '/../standard/classes/auto/_Layout.php';
require_once dirname(__FILE__) . '/../standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/../standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/../standard/classes/auto/_Page.php';

function getLayout($page_id) {
	$result = _Layout::get()->retrieve(array('layout'), array(), array('page_id' => $page_id));
	return $result[0]['layout'];
}

function getPageInformation($project, $page) {
	$user_id = Authenticate::get()->getUser('user_id');
	$id = _Page::get()->retrieve(array(), new Join('project_id', _Project::get()),
		array('project' => $project, 'page' => $page, 'user_id' => $user_id));
	if (count($id) === 1) {
		return $id[0];
	}
	return null;
}

function deleteFilesIn($folder, $exclude = array()) {
	foreach (new DirectoryIterator($folder) as $item) {
		$filename = $item->getFilename();
		if (!$item->isDot() && !in_array($filename, $exclude)) {
			if ($item->isDir()) {
				deleteFilesIn($folder . $filename . '/');
				rmdir($folder . $filename);
			} else {
				unlink($folder . $filename);
			}
		}
	}
}

function recursiveCopy($source, $dest) {
	foreach (
	$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST) as $item
	) {
		if ($item->isDir()) {
			mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
		} else {
			copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
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

function getDefaultLayout() {

	ob_start();
	?>
	<div id="content" class="-zazz-content"
			 _zazz-rid='1' _zazz-gid='1' _zazz-eid='1'><div 
			class="-zazz-outline-right -zazz-outline"> </div><div 
			class="-zazz-outline-top -zazz-outline"> </div><div 
			class="-zazz-outline-bottom -zazz-outline"> </div><div 
			class="-zazz-outline-left -zazz-outline"> </div><div 
			id="row-group-0" class="-zazz-row-group"><div 
				id="row-0" class="-zazz-row"><div 
					id="element-0" _zazz-order="1" tabindex="10" class="-zazz-element" _zazz-id="element-0"></div
				></div
			></div
		></div>
	<?php
	return ob_get_clean();
}

function createProject($project_name, $user_id) {
	$id = _Project::get()->create(array('project' => $project_name, 'user_id' => $user_id));
	$page_id = _Page::get()->create(array('page' => 'index.php', 'project_id' => $id));
	_Project::get()->update(array('default_page' => $page_id), array('project_id' => $id));
	_User::get()->update(array('active_project' => $id), array('user_id' => $user_id));
	_Code::get()->create(array('zazz_id' => 'element-0', 'page_id' => $page_id, 'type' => 'css',
		'code' => "#element-0 {\n\n}", 'zazz_order' => '0'));

	$layout = getDefaultLayout();
	
	_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout));
}

?>