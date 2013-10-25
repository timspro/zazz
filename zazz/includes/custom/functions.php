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
	<div class="-zazz-hidden">
		<div id="project-start" class="-zazz-element" tabindex="10" _zazz-id="project-start" _zazz-order="1"></div>
		<div id="project-end" class="-zazz-element" tabindex="10" _zazz-id="project-end" _zazz-order="1"></div>
		<div id="page-start" class="-zazz-element" tabindex="10" _zazz-id="page-start" _zazz-order="1"></div>
		<div id="page-end" class="-zazz-element" tabindex="10" _zazz-id="page-end" _zazz-order="1"></div>
	</div>
	<div id="content" class="-zazz-content"
			 _zazz-rid='1' _zazz-gid='1' _zazz-eid='1'><div 
			class="-zazz-outline-right -zazz-outline"> </div><div 
			class="-zazz-outline-top -zazz-outline"> </div><div 
			class="-zazz-outline-bottom -zazz-outline"> </div><div 
			class="-zazz-outline-left -zazz-outline"> </div><div 
			id="row-group-0" class="-zazz-row-group"><div 
				id="row-0" class="-zazz-row"><div 
					id="element-0" _zazz-order="1" tabindex="10" class="-zazz-element" _zazz-id="element-0"
					style="min-height: 1000px;"></div
				></div
			></div
		></div>
	<?php
	return ob_get_clean();
}

function getDefaultCSS() {
	return '/* Put project-wide CSS here. */
* {
	margin: 0px;
	padding: 0px;
	position: relative;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	vertical-align: middle;
}

:focus {
	outline: none;
}

body, html {
	width: 100%;
	height: 100%;
}

body {
	overflow-y: scroll;
}

.-zazz-content {
	width: 100%;
	vertical-align: top;
}

.-zazz-element {
	display: inline-block;
	width: 100%;
	vertical-align: top;
}

.-zazz-container {
	display: inline-block;
	width: 100%;
	vertical-align: top;
}

.-zazz-row {
	width: 100%;
	vertical-align: top;
}

.-zazz-row-group {
	display: inline-block;
	width: 100%;
	vertical-align: top;
}';
}

function getDefaultPHP() {
	return '//Note that Zazz MySQL functionality 
//depends on $_PDO referring to a PDO object.
$_PDO = new PDO("mysql:host=localhost;dbname=zazz", "root", "");
$_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);';
}

function getDefaultJS() {
	return '$.fn.center = function() {
	this.css("position", "absolute");
	this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
		$(window).scrollTop()) + "px");
	this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
		$(window).scrollLeft()) + "px");
	return this;
};';
}

function getDefaultHTMLStart() {
	return '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title> Zazz Project </title>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
	<link rel="shortcut icon" href="/zazz/css/images/zazz.ico" />
';
}

function getDefaultHTMLEnd() {
	return '<script src="js/jquery-1.10.2.js" type="text/javascript"></script>
<script src="js/functions.js" type="text/javascript"></script>
';
}

function getDefaultHTMLStartPage() {
	return '</head>
<body>
';
}

function getDefaultHTMLEndPage() {
	return '</body>
</html>';
}

function createProject($project_name, $user_id) {
	$id = _Project::get()->create(array('project' => $project_name, 'user_id' => $user_id));
	$page_id = createPage('index.php', $id);
	_User::get()->update(array('active_project' => $id), array('user_id' => $user_id));
	$start_page_id = _Page::get()->create(array('page' => '_zazz-project-start', 'project_id' => $id));
	_Code::get()->create(array('zazz_id' => 'project-start', 'page_id' => $start_page_id,
		'type' => 'css', 'code' => getDefaultCSS(), 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'project-start', 'page_id' => $start_page_id,
		'type' => 'html', 'code' => getDefaultHTMLStart(), 'zazz_order' => '2'));
	_Code::get()->create(array('zazz_id' => 'project-start', 'page_id' => $start_page_id,
		'type' => 'php', 'code' => getDefaultPHP(), 'zazz_order' => '1'));
	_Code::get()->create(array('zazz_id' => 'project-start', 'page_id' => $start_page_id,
		'type' => 'js', 'code' => getDefaultJS(), 'zazz_order' => '3'));
	$end_page_id = _Page::get()->create(array('page' => '_zazz-project-end', 'project_id' => $id));
	_Code::get()->create(array('zazz_id' => 'project-end', 'page_id' => $end_page_id, 'type' => 'html',
		'code' => getDefaultHTMLEnd(), 'zazz_order' => '2'));
	_Project::get()->update(array('default_page' => $page_id, 'project_start' => $start_page_id,
		'project_end' => $end_page_id), array('project_id' => $id));
}

function createPage($page_name, $project_id) {
	$page_id = _Page::get()->create(array('page' => $page_name, 'project_id' => $project_id));
	_Code::get()->create(array('zazz_id' => 'element-0', 'page_id' => $page_id, 'type' => 'css',
		'code' => "#element-0 {\n\n}", 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'page-start', 'page_id' => $page_id, 'type' => 'css',
		'code' => "/* Put page-wide CSS here. */", 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'page-start', 'page_id' => $page_id, 'type' => 'html',
		'code' => getDefaultHTMLStartPage(), 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'page-end', 'page_id' => $page_id, 'type' => 'html',
		'code' => getDefaultHTMLEndPage(), 'zazz_order' => '0'));

	$layout = getDefaultLayout();
	_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout));
	return $page_id;
}
?>