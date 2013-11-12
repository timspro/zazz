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
	if (file_exists($folder)) {
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
				id="begin-project" class="-zazz-element" tabindex="10" data-zazz-id="begin-project"></div><div
				id="end-project" class="-zazz-element" tabindex="10" data-zazz-id="end-project"></div><div
				id="begin-web-page" class="-zazz-element" tabindex="10" data-zazz-id="begin-web-page"></div><div
				id="end-web-page" class="-zazz-element" tabindex="10" data-zazz-id="end-web-page"></div></div><div 
			id="row-group-0" class="-zazz-row-group"><div 
				id="row-0" class="-zazz-row"><div 
					id="element-0" tabindex="10" class="-zazz-element" data-zazz-id="element-0"
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
  background-color: #dddddd;
}

.-zazz-content {
  width: 100%;
  vertical-align: top;
  max-width: 1000px;
  margin: 0 auto;
  background-color: white;
}

.-zazz-element {
  display: inline-block;
  width: 100%;
  vertical-align: top;
  border: 1px solid #c3c3c3;
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

function getDefaultPHP($dbname, $dbusername, $dbpassword) {
	return '//Note that Zazz MySQL functionality 
//depends on $ZAZZ_PDO referring to a PDO object.
$ZAZZ_PDO = new PDO(\'mysql:host=localhost;dbname=' . $dbname . '\', 
  \'' . $dbusername . '\', \'' . $dbpassword . '\');
$ZAZZ_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//ini_set("display_errors", false);
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
  <link rel="stylesheet" href="css/style.css" type="text/css" />
  <link rel="shortcut icon" href="/zazz/css/images/zazz.ico" />
';
}

function getDefaultHTMLEnd() {
	return '<!-- Note editing this file will reload page. -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" 
  type="text/javascript"></script>
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
	$userData = _User::get()->retrieve(array('dbname', 'dbusername', 'dbpassword'), array(),
		array('user_id' => $user_id));
	$id = _Project::get()->create(array('project' => $project_name, 'user_id' => $user_id));
	$page_id = createPage('home', $id);
	_User::get()->update(array('active_project' => $id), array('user_id' => $user_id));
	$start_page_id = _Page::get()->create(array('page' => 'begin-project', 'project_id' => $id));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'css', 'code' => getDefaultCSS(), 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'html', 'code' => getDefaultHTMLStart(), 'zazz_order' => '2'));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'php', 'code' => getDefaultPHP($userData[0]['dbname'], $userData[0]['dbusername'],
			$userData[0]['dbpassword']), 'zazz_order' => '1'));
	_Code::get()->create(array('zazz_id' => 'begin-project', 'page_id' => $start_page_id,
		'type' => 'js', 'code' => getDefaultJS(), 'zazz_order' => '3'));
	$end_page_id = _Page::get()->create(array('page' => 'end-project', 'project_id' => $id));
	_Code::get()->create(array('zazz_id' => 'end-project', 'page_id' => $end_page_id, 'type' => 'html',
		'code' => getDefaultHTMLEnd(), 'zazz_order' => '2'));
	_Project::get()->update(array('default_page' => $page_id, 'project_start' => $start_page_id,
		'project_end' => $end_page_id), array('project_id' => $id));
}

function createPage($page_name, $project_id, $template = '') {
	if (empty($template)) {
		$page_id = _Page::get()->create(array('page' => $page_name, 'project_id' => $project_id,
			'template' => '0'));
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
		$page_id = _Page::get()->create(array('page' => $page_name, 'project_id' => $project_id,
			'template' => $template));
		$page_id = intval($page_id);	
//		$query = Database::get()->PDO()->prepare('INSERT INTO code (zazz_id, page_id, type, code, ' .
//			'zazz_order) SELECT zazz_id, ' . $page_id . ', type, code, zazz_order FROM code WHERE ' .
//			'page_id = ' . $template);
//		$query->execute();
		$layout = _Layout::get()->retrieve('layout', array(), array('page_id' => $template));
		_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout[0]['layout']));
	}
	return $page_id;
}

function getPageCode($project_start_id, $project_end_id, $page_id, $zazz_id, $template = '') {
	$project_end_id = intval($project_end_id);
	$project_start_id = intval($project_start_id);
	$template = intval($template);
	$page_id = intval($page_id);
	//Get the code for the page.
	$query = Database::get()->PDO()->prepare("(SELECT code, type, zazz_id, zazz_order FROM code WHERE "
		. "((zazz_id IN ('begin-web-page', 'end-web-page') AND (page_id = $page_id OR page_id = $template)) OR "
		. "page_id = $project_start_id OR page_id = $project_end_id) AND type NOT IN ('css','js','html') "
		. "ORDER BY zazz_id, zazz_order, page_id DESC)");
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	//Get the code for the element.
	$query = Database::get()->PDO()->prepare("(SELECT code, type, zazz_id, zazz_order FROM code WHERE "
		. "zazz_id = :zazz_id AND (page_id = $page_id OR page_id = $template) AND type NOT IN ('css','js') "
		. "ORDER BY zazz_id, zazz_order ASC, page_id DESC)");
	$query->bindValue(':zazz_id', $zazz_id);
	$query->execute();
	$result = array_merge(array_slice($result, 0, 2), $query->fetchAll(PDO::FETCH_ASSOC),
		array_slice($result, 2));
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

function evaluate($ZAZZ_PHP) {
	eval($ZAZZ_PHP);
}

function processCode($project_start, $project_end, $page_id, $zazz_id, $basedir, $template = '') {
	if ($zazz_id === 'begin-project' || $zazz_id === 'end-project' || $zazz_id === 'begin-web-page' || $zazz_id ===
		'end-web-page') {
		return;
	}
	$_ZAZZ_BLOCKS = getPageCode($project_start, $project_end, $page_id, $zazz_id, $template);
	unset($_REQUEST);
	unset($_GET);
	unset($_POST);

	if (!file_exists($basedir)) {
		mkdir($basedir, 0777, true);
	}
	$filename = realpath($basedir);
	chdir($filename);
	ini_set('open_basedir', $filename);

	$php = '';
	$css = '';
	$js = '';
	$zazz_order = -1;
	$zazz_id = '!';
	foreach ($_ZAZZ_BLOCKS as $_ZAZZ_BLOCK) {
		if($zazz_order !== intval($_ZAZZ_BLOCK['zazz_order']) || $zazz_id !== $_ZAZZ_BLOCK['zazz_id']) {
			processBlock($_ZAZZ_BLOCK, $php, $css, $js, $php, false);
			$zazz_id = $_ZAZZ_BLOCK['zazz_id'];
			$zazz_order = intval($_ZAZZ_BLOCK['zazz_order']);
		}
	}

	evaluate($php);
}

function prepareQuery($query) {
	$params = GetParametersForQuery($query);
	$code = "\n<?php\nob_start();\n?>\n" . $query .
		"\n<?php\n" . '$_ZAZZ_PDO_CODE = ob_get_clean();' . "\n" .
		'$_ZAZZ_PDO_QUERY = $ZAZZ_PDO->prepare($_ZAZZ_PDO_CODE);';
	foreach ($params as $param) {
		$code .= "\n" . '$_ZAZZ_PDO_QUERY->bindValue(\':' . $param . '\', $' . $param . ');';
	}
	$code .= "\n" . '$_ZAZZ_PDO_QUERY->execute();' . "\n" .
		'$ZAZZ_ROWS = $_ZAZZ_PDO_QUERY->fetchAll(PDO::FETCH_ASSOC);' . "\n?>\n";
	return $code;
}

function processBlock($block, &$php, &$css, &$js, &$html, $phptags = true) {
	switch ($block['type']) {
		case 'css':
			$css .= $block['code'] . "\n";
			break;
		case 'html':
			if ($phptags) {
				$html .= $block['code'] . "\n";
			} else {
				$html .= "?>\n" . $block['code'] . "\n<?php\n;\n";
			}
			break;
		case 'mysql':
			if (!empty($block['code'])) {
				$php .= prepareQuery($block['code']);
			}
			break;
		case 'php':
			if ($phptags) {
				$php .= "<?php\n" . $block['code'] . "\n?>\n";
			} else {
				$php .= $block['code'] . "\n";
			}
			break;
		case 'js':
			$js .= $block['code'] . "\n\n";
			break;
	}
}

function getComputedLayout($project_start, $project_end, $page_id, $basedir, $template) {
	require_once dirname(__FILE__) . '/simple_html_dom.php';
	$layout = new simple_html_dom();
	$layout->load(getLayout($page_id));
	foreach ($layout->find('.-zazz-element') as $element) {
		ob_start();
		processCode($project_start, $project_end, $page_id, $element->getAttribute('data-zazz-id'),
			$basedir, $template);
		$element->innertext = ob_get_clean();
	}
	return $layout->save();
}

function validateFilename($filename) {
	if (preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename) !== $filename || str_replace('..', '',
			$filename) !== $filename) {
		return false;
	}
	return true;
}
?>