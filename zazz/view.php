<?php
require_once dirname(__FILE__) . '/includes/standard/initialize.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';
require_once dirname(__FILE__) . '/includes/custom/functions.php';

Authenticate::get()->check();

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
$page = $_GET['page'];

$page_id = getPageID($project, $page);
if(empty($page_id)) {
	$project = _Project::get()->retrieve('project', array(),
		array('project_id' =>
		Authenticate::get()->getUser('active_project')));
	header('Location: /zazz/view/' . $project[0]['project'] . '/index.php');
	return;
}
$user_id = Authenticate::get()->getUser('user_id');
$filename = dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/';

//Perhaps already created.
if(!file_exists($filename)) {
	mkdir($filename, 0777, true);
}
	
//Perhaps old files are there.
deleteFilesIn($filename);

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
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Zazz</title>
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
$check = array();
foreach($blocks as $block) {
	switch($block['type']) {
		case 'css':
			$css .= $block['code'] . "\n\n";
			break;
		case 'html':
			$element = $html->find('#' . $block['zazz_id'], 0);
			if(!isset($check[$block['zazz_id']])) {
				$element->innertext = $block['code'] . "\n\n"; 
				$check[$block['zazz_id']] = true;
			} else {
				$element->innertext .= $block['code'] . "\n\n"; 				
			}
			break;
		case 'mysql':
			//TODO
			break;
		case 'php':
			$element = $html->find('#' . $block['zazz_id'], 0);
			if(!isset($check[$block['zazz_id']])) {
				$element->innertext = "<?php\n" .$block['code'] . "\n?>\n\n";
				$check[$block['zazz_id']] = true;
			} else {
				$element->innertext .= "<?php\n" .$block['code'] . "\n?>\n\n";
			}
			break;
		case 'js':
			$js .= $block['code'] . "\n\n";
			break;
	}
}

foreach($html->find('.-zazz-outline') as $outline) {
	$outline->outertext = "";
}

$js .= "});";
mkdir($filename . 'js/');	
file_put_contents($filename . 'js/functions.js', $js);
$source = dirname(__FILE__) . '/js/jquery-1.10.2.js';
$dest = $filename . 'js/jquery-1.10.2.js';
copy($source, $dest);
mkdir($filename . 'css/');	
file_put_contents($filename . 'css/style.css', $css);

$php .= $html->save() . '
</body>
	<script src="js/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="js/functions.js" type="text/javascript"></script>
</html>
';

file_put_contents($filename. $page, $php);

header('Location: /zazz/view/' . $user_id . '/' . $project . '/' . $page);

?>