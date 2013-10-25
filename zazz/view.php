<?php
require_once dirname(__FILE__) . '/includes/standard/initialize.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';
require_once dirname(__FILE__) . '/includes/custom/functions.php';

Authenticate::get()->check();
$user_id = Authenticate::get()->getUser('user_id');

function addCode(&$html, $id, $code) {
	static $check = array();
	$element = $html->find('.-zazz-element[data-zazz-id="' . $id . '"]', 0);
	echo $id . '<br>';
	if (!isset($check[$id])) {
		$element->innertext = $code;
		$check[$id] = true;
	} else {
		$element->innertext .= $code;
	}
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

function processBlock($block, &$php, &$css, &$js) {
	switch ($block['type']) {
		case 'css':
			$css .= $block['code'] . "\n";
			break;
		case 'html':
			$php .= $block['code'] . "\n";
			break;
		case 'mysql':
			if (!empty($block['code'])) {
				$php .= prepareQuery($block['code']);
			}
			break;
		case 'php':
			$php .= "<?php\n" . $block['code'] . "\n?>\n";
			break;
		case 'js':
			$js .= $block['code'] . "\n\n";
			break;
	}
}

//Do we know the project?
if (!isset($_GET['project'])) {
	$project = _Project::get()->retrieve(array('project', 'default_page'), array(),
		array('project_id' => Authenticate::get()->getUser('active_project')));
	header('Location: /zazz/view/' . $project[0]['project'] . '/' . $project[0]['default_page']);
	return;
}
$project = $_GET['project'];

//Is it a valid project ID?
$project_id = _Project::get()->retrieve(array('project_id', 'project_start', 'project_end'),
	array(), array('project' => $project, 'user_id' => $user_id));
if (empty($project_id)) {
	$project = _Project::get()->retrieve(array('project', 'default_page'), array(),
		array('project_id' => Authenticate::get()->getUser('active_project')));
	header('Location: /zazz/view/' . $project[0]['project'] . '/' . $project[0]['default_page']);
	return;
} else {
	$project_start = $project_id[0]['project_start'];
	$project_end = $project_id[0]['project_end'];
	$project_id = $project_id[0]['project_id'];
}

//Set up the folder to put the project in.
$filename = dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/';

//Figure out what pages we need to generate.
if (isset($_GET['deploy'])) {
	$generate_pages = _Page::get()->retrieve('page', new Join('project_id', _Project::get()),
		array('project' =>
		$project));
	for ($i = 0; $i < count($generate_pages); $i++) {
		if ($generate_pages[$i]['page'] === 'data-zazz-project-start') {
			$start = $i;
		}
		if($generate_pages[$i]['page'] === 'data-zazz-project-end') {
			$end = $i;
		}
	}
	unset($generate_pages[$start]);
	unset($generate_pages[$end]);
	deleteFilesIn($filename, array('css'));
} else if (isset($_GET['page'])) {
	$generate_pages = array(array('page' => $_GET['page']));
} else {
	$default_page = _Project::get()->retrieve(array('default_page'), array(),
		array('project_id' => $project_id));
	header('Location: /zazz/view/' . $project . '/' . $default_page[0]['default_page']);
	return;
}

//Create folders.
if (!file_exists($filename)) {
	mkdir($filename, 0777, true);
}
if (!file_exists($filename . 'css/')) {
	mkdir($filename . 'css/');
}
if (!file_exists($filename . 'js/')) {
	mkdir($filename . 'js/');
}

//Process the shared information such as (css, js) and get the header of the document (php, mysql, html).
$project_code_start = _Code::get()->retrieve(array('code', 'type'), array(),
	array('page_id' => $project_start), 'zazz_order');
$project_code_end = _Code::get()->retrieve(array('code', 'type'), array(),
	array('page_id' => $project_end), 'zazz_order');
$project_css = '';
$project_js = '$(document).ready(function() {
';
$project_php_header = '';
foreach ($project_code_start as $block) {
	processBlock($block, $project_php_header, $project_css, $project_js);
}

$project_php_footer = '';
foreach ($project_code_end as $block) {
	processBlock($block, $project_php_footer, $project_css, $project_js);
}

$project_js .= '});';

file_put_contents($filename . 'js/functions.js', $project_js);
file_put_contents($filename . 'css/style.css', $project_css);

foreach ($generate_pages as $generate_page) {
	$page = $generate_page['page'];
	$page_info = getPageInformation($project, $page);
	$page_id = $page_info['page_id'];
	if (empty($page_id)) {
		//Perhaps just a resource file. Still need to check that the user owns the project and that 
		//$page doesn't have ..
		$result = _Project::get()->retrieve('user_id', array(), array('project' => $project));
		if (empty($result) || $result[0]['user_id'] !== $user_id) {
			echo 'Could not find project specified.';
			return;
		}
		if (strrpos($page, '..') !== false) {
			echo 'You may not use ".." in the page name.</div>';
			return;
		}
		$resource = $filename . $page;
		if (file_exists($resource)) {
			$extension = pathinfo($resource, PATHINFO_EXTENSION);
			if ($extension === 'css') {
				$mime = 'text/css';
			} else if ($extension === 'js') {
				$mime = 'application/javascript';
			} else {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $resource);
				finfo_close($finfo);
			}
			header("Content-type: " . $mime);
			readfile($resource);
			return;
		} else {
			echo 'There was a serious error in getting the page information for ' . $page .
			' in ' . $project;
			return;
		}
	}

//-------------------------------------INITIALIZE------------------------------------------

	$css = '';
	$js = '$(document).ready(function() {
';
	$php = '';
	$php_header = $project_php_header;
	$php_footer = $project_php_footer;

	$blocks = _Code::get()->retrieve(array('code', 'type', 'zazz_id'), array(),
		array('page_id' => $page_id), 'zazz_order');
	for ($i = 0; $i < count($blocks); $i++) {
		if ($blocks[$i]['zazz_id'] === 'page-start') {
			$start_index = $i;
		}
		if ($blocks[$i]['zazz_id'] === 'page-end') {
			$end_index = $i;
		}
	}

	processBlock($blocks[$start_index], $php_header, $css, $js);
	processBlock($blocks[$end_index], $php_footer, $css, $js);
	unset($blocks[$start_index]);
	unset($blocks[$end_index]);

	$layout = $php_header . getLayout($page_id) . $php_footer;
	require_once dirname(__FILE__) . '/includes/custom/simple_html_dom.php';
	$html = new simple_html_dom();
	$html->load($layout);
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
					$code = prepareQuery($block['code']);
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
	$element = $html->find('.-zazz-hidden', 0);
	if ($element) {
		$element->outertext = '';
	}

	foreach ($html->find('.-zazz-element') as $outline) {
		$outline->setAttribute('data-zazz-id', null);
		$outline->setAttribute('data-zazz-order', null);
	}

	$content = $html->find('#content', 0);
	$content->setAttribute('data-zazz-rid', null);
	$content->setAttribute('data-zazz-gid', null);
	$content->setAttribute('data-zazz-eid', null);

	$js .= "});";
	$html->find('body', 0)->innertext .= '<script>
' . $js . '
</script>';

	$html->find('head', 0)->innertext .= '<style>
' . $css . '
</style>';

	$php .= $html->save();

	file_put_contents($filename . $page, $php);
}

$source = dirname(__FILE__) . '/js/jquery-1.10.2.js';
$dest = $filename . 'js/jquery-1.10.2.js';
copy($source, $dest);

if (isset($_GET['deploy'])) {
	deleteFilesIn(dirname(__FILE__) . '/../', array('zazz', 'nbproject', '.git', '.gitignore'));
	recursiveCopy(dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/',
		dirname(__FILE__) . '/../');
	header('Location: /' . $_GET['page']);
} else {
	require_once dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/' . $_GET['page'];
}
?>