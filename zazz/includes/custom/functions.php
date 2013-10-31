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
	$check = _Project::get()->retrieve(array(), new Join('project_id', _Page::get()),
		array('page_id' => $page_id, 'user_id' => $user_id));
	if ($check > 0) {
		return $check;
	}
	return false;
}

function getDefaultLayout() {

	ob_start();
	?>
	<div id="content" class="-zazz-content"
			 data-zazz-rid='1' data-zazz-gid='1' data-zazz-eid='1'><div class="-zazz-hidden"><div
				id="begin-project" class="-zazz-element" tabindex="10" data-zazz-id="begin-project" data-zazz-order="1"></div><div
				id="end-project" class="-zazz-element" tabindex="10" data-zazz-id="end-project" data-zazz-order="1"></div><div
				id="begin-web-page" class="-zazz-element" tabindex="10" data-zazz-id="begin-web-page" data-zazz-order="1"></div><div
				id="end-web-page" class="-zazz-element" tabindex="10" data-zazz-id="end-web-page" data-zazz-order="1"></div></div><div 
			id="row-group-0" class="-zazz-row-group"><div 
				id="row-0" class="-zazz-row"><div 
					id="element-0" data-zazz-order="1" tabindex="10" class="-zazz-element" data-zazz-id="element-0"
					style="min-height: 1000px;"></div
				></div
			></div
		></div>
	<?php
	return ob_get_clean();
}

function getDefaultCSS() {
	return '/* Put project-wide CSS here. 
Note that URLs must start with 
"resources/" (as opposed to 
"css/resources/" for other CSS)
since this CSS is written 
to a file in the "css" folder,
while other CSS is inlined.*/
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
	background-color: #6699ff;
}

.-zazz-content {
	width: 100%;
	vertical-align: top;
}

.-zazz-element {
	display: inline-block;
	width: 100%;
	vertical-align: top;
	border: 2px dashed blue;
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
//depends on $ZAZZ_PDO referring to a PDO object.
$ZAZZ_PDO = new PDO("mysql:host=localhost;dbname=zazz", "root", "");
$ZAZZ_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
';
}

function getDefaultJS() {
	return '$.fn.center = function() {
  this.css("position", "absolute");
  this.css("top", Math.max(0, (($(window).height() - 
	  $(this).outerHeight()) / 2) + $(window).scrollTop()) + "px");
  this.css("left", Math.max(0, (($(window).width() - 
	  $(this).outerWidth()) / 2) + $(window).scrollLeft()) + "px");
  return this;
};
';
}

function getDefaultHTMLStart() {
	return '<!DOCTYPE html>
<!-- Note editing this file will reload page. -->
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title> Zazz Project </title>
  <!-- DO NOT EDIT NEXT LINE -->
  <link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
  <link rel="shortcut icon" href="/zazz/css/images/zazz.ico" />
';
}

function getDefaultHTMLEnd() {
	return '<!-- Note editing this file will reload page. -->
<script src="js/jquery-1.10.2.js" type="text/javascript"></script>
<!-- DO NOT EDIT NEXT LINE -->
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
</html>
';
}

function createProject($project_name, $user_id) {
	$id = _Project::get()->create(array('project' => $project_name, 'user_id' => $user_id));
	$page_id = createPage('index.php', $id);
	_User::get()->update(array('active_project' => $id), array('user_id' => $user_id));
	$start_page_id = _Page::get()->create(array('page' => 'begin-project', 'project_id' => $id));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'css', 'code' => getDefaultCSS(), 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'html', 'code' => getDefaultHTMLStart(), 'zazz_order' => '2'));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'php', 'code' => getDefaultPHP(), 'zazz_order' => '1'));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'js', 'code' => getDefaultJS(), 'zazz_order' => '3'));
	$end_page_id = _Page::get()->create(array('page' => 'end-project', 'project_id' => $id));
	_Code::get()->create(array('zazz_id' => 'end-project', 'page_id' => $end_page_id, 'type' => 'html',
		'code' => getDefaultHTMLEnd(), 'zazz_order' => '2'));
	_Project::get()->update(array('default_page' => $page_id, 'project_start' => $start_page_id,
		'project_end' => $end_page_id), array('project_id' => $id));
}

function createPage($page_name, $project_id, $template = '') {
	$page_id = _Page::get()->create(array('page' => $page_name, 'project_id' => $project_id));
	if (empty($template)) {
		_Code::get()->create(array('zazz_id' => 'element-0', 'page_id' => $page_id, 'type' => 'css',
			'code' => "#element-0 {\n\n}", 'zazz_order' => '0'));
		_Code::get()->create(array('zazz_id' => 'begin-web-page', 'page_id' => $page_id, 'type' => 'css',
			'code' => "/* Put page-wide CSS here. */", 'zazz_order' => '0'));
		_Code::get()->create(array('zazz_id' => 'begin-web-page', 'page_id' => $page_id, 'type' => 'html',
			'code' => getDefaultHTMLStartPage(), 'zazz_order' => '0'));
		_Code::get()->create(array('zazz_id' => 'end-web-page', 'page_id' => $page_id, 'type' => 'html',
			'code' => getDefaultHTMLEndPage(), 'zazz_order' => '0'));

		$layout = getDefaultLayout();
		_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout));
	} else {
		$template = _Page::get()->retrieve('page_id', array(),
			array('page' => $template,
			'project_id' => $project_id));
		$template = intval($template[0]['page_id']);
		$page_id = intval($page_id);
		$query = Database::get()->PDO()->prepare('INSERT INTO code (zazz_id, page_id, type, code, ' .
			'zazz_order) SELECT zazz_id, ' . $page_id . ', type, code, zazz_order FROM code WHERE ' .
			'page_id = ' . $template);
		$query->execute();
		$layout = _Layout::get()->retrieve('layout', array(), array('page_id' => $template));
		_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout[0]['layout']));
	}
	return $page_id;
}

function getPageCode($project_start_id, $project_end_id, $page_id, $zazz_id) {
	$project_end_id = intval($project_end_id);
	$project_start_id = intval($project_start_id);
	$page_id = intval($page_id);
	$query = Database::get()->PDO()->prepare("(SELECT code, type FROM code WHERE "
		. "((zazz_id IN ('begin-web-page', 'end-web-page') AND page_id = $page_id) OR "
		. "page_id = $project_start_id OR page_id = $project_end_id) AND type NOT IN ('css','js','html') "
		. "ORDER BY zazz_id, zazz_order) UNION ALL (SELECT code, type FROM code WHERE zazz_id = :zazz_id "
		. "AND page_id = $page_id ORDER BY zazz_order)");
	$query->bindValue(':zazz_id', $zazz_id);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	return $result;
}

function zipFolder($source, $destination) {
	if (!extension_loaded('zip') || !file_exists($source)) {
		return false;
	}

	$zip = new ZipArchive();
	if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
		return false;
	}

	$source = realpath($source) . DIRECTORY_SEPARATOR;
	foreach ($iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST) as $item) {
		$filename = realpath($item->getPathname());
		if ($item->isDir()) {
			$zip->addEmptyDir(str_replace($source, '', $filename));
		} else {
			$zip->addFromString(str_replace($source, '', $filename), file_get_contents($filename));
		}
	}
}

function processCode($project_start, $project_end, $page_id, $zazz_id, $basedir) {
	if ($zazz_id === 'begin-project' || $zazz_id === 'end-project' || $zazz_id === 'begin-web-page' || $zazz_id ===
		'end-web-page') {
		return;
	}
	$_ZAZZ_BLOCKS = getPageCode($project_start, $project_end, $page_id, $zazz_id);
	unset($_REQUEST);
	unset($_GET);
	unset($_POST);
	
	if(!file_exists($basedir)) {
		mkdir($basedir, 0777, true);
	}
	$filename = realpath($basedir);
	chdir($filename);
	ini_set('open_basedir', $filename);
	
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

function getComputedLayout($project_start, $project_end, $page_id, $basedir) {
	require_once dirname(__FILE__) . '/simple_html_dom.php';
	$layout = new simple_html_dom();
	$layout->load(getLayout($page_id));
	foreach ($layout->find('.-zazz-element') as $element) {
		ob_start();
		processCode($project_start, $project_end, $page_id,	$element->getAttribute('data-zazz-id'),
			$basedir);
		$element->innertext = ob_get_clean();
	}
	return $layout->save();
}
?>