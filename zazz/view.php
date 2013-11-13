<?php
require_once dirname(__FILE__) . '/includes/standard/initialize.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';
require_once dirname(__FILE__) . '/includes/custom/functions.php';

Authenticate::get()->check();
$user_id = Authenticate::get()->getUser('user_id');

if (isset($_GET['deploy']) && $_GET['deploy'] !== /* !_!_!PASSWORD!_!_! */'NEPOm20dkP_e3ls0elOEMlsoW'/* !_!_!PASSWORD!_!_! */) {
	echo 'You did not enter the right password.';
	return;
}

function addCode(&$html, $id, $code, $check) {
	$element = $html->find('.-zazz-element[data-zazz-id="' . $id . '"]', 0);
	if (!isset($check[$id])) {
		$element->innertext = $code;
		$check[$id] = true;
	} else {
		$element->innertext .= $code;
	}
}

function serveFile($resource) {
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
		return true;
	} else {
		return false;
	}
}

function redirectToDefault() {
	$project = _Project::get()->retrieve(array('project', 'default_page'), array(),
		array('project_id' => Authenticate::get()->getUser('active_project')));
	$default_page = _Page::get()->retrieve('page', array(), array('page_id' => $project[0]['default_page']));
	header('Location: /zazz/view/' . $project[0]['project'] . '/' . $default_page[0]['page']);	
}

//Do we know the project?
if (!isset($_GET['project'])) {
	redirectToDefault();
	return;
}
$project = $_GET['project'];

//Is it a valid project ID?
$project_id = _Project::get()->retrieve(array('project_id', 'project_start', 'project_end', 
	'default_page'), array(), array('project' => $project, 'user_id' => $user_id));
if (empty($project_id)) {
	redirectToDefault();
	return;
} else {
	$default_page = $project_id[0]['default_page'];
	$project_start = $project_id[0]['project_start'];
	$project_end = $project_id[0]['project_end'];
	$project_id = $project_id[0]['project_id'];
}

//Set up the folder to put the project in.
$filename = dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/';
if (!file_exists($filename)) {
	mkdir($filename, 0777, true);
}

//Figure out what pages we need to generate.
if (isset($_GET['deploy']) || isset($_GET['export'])) {
	$generate_pages = _Page::get()->retrieve('page', new Join('project_id', _Project::get()),
		array('project' => $project, 'user_id' => $user_id));
	for ($i = 0; $i < count($generate_pages); $i++) {
		if ($generate_pages[$i]['page'] === 'begin-project') {
			$start = $i;
		}
		if ($generate_pages[$i]['page'] === 'end-project') {
			$end = $i;
		}
	}
	unset($generate_pages[$start]);
	unset($generate_pages[$end]);
	deleteFilesIn($filename, array('css', 'js'));
} else if (isset($_GET['page'])) {
	$generate_pages = array(array('page' => $_GET['page']));
} else {
	redirectToDefault();
	return;
}

//Create folders.
if (!file_exists($filename . 'css/')) {
	mkdir($filename . 'css/');
}
if (!file_exists($filename . 'js/')) {
	mkdir($filename . 'js/');
}
if (!file_exists($filename . 'includes/')) {
	mkdir($filename . 'includes/');
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
$project_html_header = '';
$project_html_footer = '';
foreach ($project_code_start as $block) {
	processBlock($block, $project_php_header, $project_css, $project_js, $project_html_header);
}

$project_php_footer = '';
foreach ($project_code_end as $block) {
	processBlock($block, $project_php_footer, $project_css, $project_js, $project_html_footer);
}

$project_js .= '});';

file_put_contents($filename . 'js/functions.js', $project_js);
file_put_contents($filename . 'css/style.css', $project_css);
file_put_contents($filename . 'includes/header.php', $project_php_header);
file_put_contents($filename . 'includes/footer.php', $project_php_footer);


foreach ($generate_pages as $generate_page) {
	$page = $generate_page['page'];
	$page_info = getPageInformation($project, $page);
	$page_id = intval($page_info['page_id']);
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
		if (!serveFile($resource)) {
			echo 'There was a serious error in getting the page information for ' . $page .
			' in ' . $project;
			return;
		}
		return;
	}
	//Don't show hidden pages.
	if (empty($page_info['visible'])) {
		continue;
	}	
	
//-------------------------------------INITIALIZE------------------------------------------

	$css = '';
	$js = '$(document).ready(function() {
';
	$php = '';
	$php_header = '<?php include_once dirname(__FILE__) . "/includes/header.php"; ?>
';
	$php_footer = '<?php include_once dirname(__FILE__) . "/includes/footer.php"; ?>
';
	$html_header = $project_html_header;
	$html_footer = $project_html_footer;

	$template = intval($page_info['template']);
	$query = Database::get()->PDO()->prepare("SELECT code, type, zazz_id, zazz_order, page_id FROM code WHERE page_id = 
		$page_id OR page_id = $template ORDER BY zazz_id ASC, zazz_order ASC, page_id DESC");
	$query->execute();
	$blocks = $query->fetchAll(PDO::FETCH_ASSOC);
	$start_index = array();
	$end_index = array();
	$foundBegin = 0;
	$foundEnd = 0;

	for ($i = 0; $i < count($blocks); $i++) {
		if ($blocks[$i]['zazz_id'] === 'begin-web-page') {
			if (empty($foundBegin) || $blocks[$i]['page_id'] === $foundBegin) {
				$foundBegin = $blocks[$i]['page_id'];
				processBlock($blocks[$i], $php_header, $css, $js, $html_header);
			}
			$start_index[] = $i;
		} else if ($blocks[$i]['zazz_id'] === 'end-web-page') {
			if (empty($foundEnd) || $blocks[$i]['page_id'] === $foundEnd) {
				$foundEnd = $blocks[$i]['page_id'];
				processBlock($blocks[$i], $php_footer, $css, $js, $html_footer);
			}
			$end_index[] = $i;
		}
	}

	foreach ($start_index as $index) {
		unset($blocks[$index]);
	}
	foreach ($end_index as $index) {
		unset($blocks[$index]);
	}

	$layout = $html_header . getLayout($page_id) . $html_footer;
	require_once dirname(__FILE__) . '/includes/custom/simple_html_dom.php';
	$html = new simple_html_dom();
	$html->load($layout);
	$zazz_order = -1;
	$zazz_id = '!';
	$check = array();
	foreach ($blocks as $block) {
		if ($zazz_id !== $block['zazz_id'] || $zazz_order !== $block['zazz_order']) {
			switch ($block['type']) {
				case 'css':
					$css .= $block['code'] . "\n\n";
					break;
				case 'html':
					addCode($html, $block['zazz_id'], "\n" . $block['code'] . "\n", $check);
					break;
				case 'mysql':
					if (!empty($block['code'])) {
						$code = prepareQuery($block['code']);
						addCode($html, $block['zazz_id'], $code, $check);
					}
					break;
				case 'php':
					addCode($html, $block['zazz_id'], "\n<?php\n" . $block['code'] . "\n?>\n", $check);
					break;
				case 'js':
					$js .= $block['code'] . "\n\n";
					break;
			}
			$zazz_id = $block['zazz_id'];
			$zazz_order = $block['zazz_order'];
		}
	}

	foreach ($html->find('.-zazz-outline') as $outline) {
		$outline->class = str_replace('-zazz-outline', '', $outline->class);
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

	file_put_contents($filename . $page, $php_header . $php . $php_footer);
}

//$source = dirname(__FILE__) . '/js/jquery-1.10.2.js';
//$dest = $filename . 'js/jquery-1.10.2.js';
//copy($source, $dest);

if (isset($_GET['deploy'])) {
	deleteFilesIn(dirname(__FILE__) . '/../',
		array('zazz', 'nbproject', '.git', '.gitignore',
		'.htaccess'));
	recursiveCopy(dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/',
		dirname(__FILE__) . '/../');
	//Update .htaccess with directory index.
	$contents = file_get_contents(dirname(__FILE__) . '/../.htaccess');
	$token = '#!_!_!DEFAULTPAGE!_!_!';
	$quoted_token = preg_quote($token, '/');
	$default_page_name = _Page::get()->retrieve('page', array(), array('page_id' => $default_page));
	$contents = preg_replace('/' . $quoted_token . '.*?' . $quoted_token . '/s',
		$token . "\nDirectoryIndex " . $default_page_name[0]['page'] . "\n" . $token, $contents, 1);
	file_put_contents(dirname(__FILE__) . '/../.htaccess', $contents);
	
	header('Location: /');
} else if (isset($_GET['export'])) {
	$filename = dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/';
	if (!file_exists($filename . 'zip/')) {
		mkdir($filename . 'zip/');
	}
	deleteFilesIn($filename . 'zip/');
	$resource = $filename . 'zip/' . $project . '.zip';
	zipFolder($filename, $resource);
	if (!serveFile($resource)) {
		echo 'There was a serious error in getting the page information for ' . $page .
		' in ' . $project;
		return;
	}
} else {
	$basedir = dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/';
	$filename = realpath($basedir);
	chdir($filename);
	ini_set('open_basedir', $filename);
	require_once dirname(__FILE__) . '/view/' . $user_id . '/' . $project . '/' . $_GET['page'];
}
?>