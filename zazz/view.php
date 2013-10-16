<?php
require_once dirname(__FILE__) . '/includes/standard/initialize.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';
require_once dirname(__FILE__) . '/includes/custom/functions.php';

Authenticate::get()->check();

function addCode(&$html, $id, $code) {
	static $check = array();
	$element = $html->find('.-zazz-element[_zazz-id="' . $id . '"]', 0);
	if (!isset($check[$id])) {
		$element->innertext = $code;
		$check[$id] = true;
	} else {
		$element->innertext .= $code;
	}
}

if (!isset($_GET['project'])) {
	$project = _Project::get()->retrieve('project', array(),
		array('project_id' =>
		Authenticate::get()->getUser('active_project')));
	header('Location: /zazz/view/' . $project[0]['project'] . '/index.php');
	return;
}
$project = $_GET['project'];

if (!isset($_GET['page'])) {
	header('Location: /zazz/view/' . $project . '/index.php');
	return;
}

$user_id = Authenticate::get()->getUser('user_id');
$filename = dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/';

//Perhaps already created.
if (!file_exists($filename)) {
	mkdir($filename, 0777, true);
}

if (isset($_GET['deploy'])) {
	$generate_pages = _Page::get()->retrieve('page', new Join('project_id', _Project::get()),
		array('project' =>
		$project));
	deleteFilesIn($filename);
} else {
	$generate_pages = array(array('page' => $_GET['page']));
}

foreach ($generate_pages as $generate_page) {
	$page = $generate_page['page'];
	$page_info = getPageInformation($project, $page);
	$page_id = $page_info['page_id'];
	if (empty($page_id)) {
		echo 'There was a serious error in getting the page information for ' . $page . 
			' in ' . $project;
		return;
	}

//-------------------------------------INITIALIZE------------------------------------------

	$js = '$(document).ready(function() {

';
	$css = '
* {
	margin: 0px;
	padding: 0px;
	position: relative;
	overflow: hidden;
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
	height: 1000px;
}

.-zazz-element {
	height: 100%;
	width: 100%;
	/*background-image: url("img/blue.png");*/
	/*border: #0000cc dashed 2px;*/
	display: inline-block;
}

.-zazz-row {
	height: 100%;
	width: 100%;
}

';

	$php = '
<?php 
$_PDO = new PDO("mysql:host=localhost;dbname=zazz", "root", "");
$_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>' . $project . '</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
</head>
<body>
';

	$layout = getLayout($page_id);
	require_once dirname(__FILE__) . '/includes/custom/simple_html_dom.php';
	$html = new simple_html_dom();
	$html->load($layout);
	$blocks = _Code::get()->retrieve(array('code', 'type', 'zazz_id'), array(),
		array('page_id' => $page_id), 'zazz_order');
	foreach ($blocks as $block) {
		switch ($block['type']) {
			case 'css':
				$css .= $block['code'] . "\n\n";
				break;
			case 'html':
				addCode($html, $block['zazz_id'], "\n" . $block['code'] . "\n");
				break;
			case 'mysql':
				if (!empty($block['code'])) {
					$params = GetParametersForQuery($block['code']);
					$code = "\n<?php\nob_start();\n?>\n" . $block['code'] .
						"\n<?php\n" . '$_PDO_CODE = ob_get_clean();' . "\n" .
						'$_PDO_QUERY = $_PDO->prepare($_PDO_CODE);';
					foreach ($params as $param) {
						$code .= "\n" . '$_PDO_QUERY->bindValue(\':' . $param . '\', $' . $param . ');';
					}
					$code .= "\n" . '$_PDO_QUERY->execute();' . "\n" .
						'$_ROWS = $_PDO_QUERY->fetchAll(PDO::FETCH_ASSOC);' . "\n?>\n";
					addCode($html, $block['zazz_id'], $code);
				}
				break;
			case 'php':
				addCode($html, $block['zazz_id'], "\n<?php\n" . $block['code'] . "\n?>\n");
				break;
			case 'js':
				$js .= $block['code'] . "\n\n";
				break;
		}
	}

	foreach ($html->find('.-zazz-outline') as $outline) {
		$outline->outertext = "";
	}

	foreach ($html->find('.-zazz-element') as $outline) {
		$outline->setAttribute('_zazz-id', null);
		$outline->setAttribute('_zazz-order', null);
	}

	$content = $html->find('#content', 0);
	$content->setAttribute('_zazz-rid', null);
	$content->setAttribute('_zazz-gid', null);
	$content->setAttribute('_zazz-eid', null);

	$js .= "});";
	if (!file_exists($filename . 'js/')) {
		mkdir($filename . 'js/');
	}
	file_put_contents($filename . 'js/functions.js', $js);
	$source = dirname(__FILE__) . '/js/jquery-1.10.2.js';
	$dest = $filename . 'js/jquery-1.10.2.js';
	copy($source, $dest);
	if (!file_exists($filename . 'css/')) {
		mkdir($filename . 'css/');
	}
	file_put_contents($filename . 'css/style.css', $css);

	$php .= $html->save() . '
</body>
	<script src="js/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="js/functions.js" type="text/javascript"></script>
</html>
';

	file_put_contents($filename . $page, $php);
}

if (isset($_GET['deploy'])) {
	deleteFilesIn(dirname(__FILE__) . '/../', array('zazz', 'nbproject', '.git', '.gitignore'));
	recursiveCopy(dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/',
		dirname(__FILE__) . '/../');
	header('Location: /' . $_GET['page']);
} else {
	require_once dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/' . $_GET['page'];
}
?>