<?php

function getCodeBlocks($page_id, $project_start, $project_end) {
	$page_id = intval($page_id);
	$project_start = intval($project_start);
	$project_end = intval($project_end);
	$query = Database::get()->PDO()->prepare("SELECT zazz_id, code, type, zazz_order FROM code WHERE " .
		"page_id = $page_id OR page_id = $project_start OR page_id = $project_end ORDER BY zazz_order");
	$query->execute();
	$rows = $query->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		echo '<textarea class="-zazz-code-block -zazz-' . $row["type"] . '-code 
							-zazz-code-block-' . $row['zazz_id'] . '" 
							spellcheck="false" tabindex="10" data-zazz-order="' . $row['zazz_order'] . '" 
							style="display: none;" >' . $row['code'] . '</textarea>';
	}
}

function getDefaultCodeBlock() {
	echo '<textarea class="-zazz-code-block -zazz-css-code 
							-zazz-code-block-element-0" 
							spellcheck="false" tabindex="10" data-zazz-order="0" 
							style="display: none;" >#element-0 { ' . "\n\n" . '}</textarea>
				<textarea class="-zazz-code-block -zazz-css-code 
							-zazz-code-block-page" 
							spellcheck="false" tabindex="10" data-zazz-order="0" 
							style="display: none;" >#page { ' . "\n\n" . '}</textarea>
				<textarea class="-zazz-code-block -zazz-css-code 
							-zazz-code-block-project" 
							spellcheck="false" tabindex="10" data-zazz-order="0" 
							style="display: none;" >#project { ' . "\n\n" . '}</textarea>';
}

if (isset($_GET['demo'])) {
	$demo = true;
	session_start();
	session_destroy();
} else {
	$demo = false;
}

require_once dirname(__FILE__) . '/includes/custom/functions.php';

if (!$demo) {

	require_once dirname(__FILE__) . '/includes/standard/initialize.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';

	Authenticate::get()->check();
	$user_id = Authenticate::get()->getUser('user_id');

	if (!isset($_GET['project']) || empty($_GET['project'])) {
		$active_id = Authenticate::get()->getUser('active_project');
		$project = _Project::get()->retrieve('project', array(), array('project_id' => $active_id));
		if (empty($project[0]['project'])) {
			echo 'There has been a serious error.';
			return;
		}
		$check = _Project::get()->retrieve('project_id', array(),
			array('project' => $project[0]['project']));
		if (empty($check)) {
			echo 'There has been a serious error.';
			return;
		}
		header('Location: /zazz/build/' . $project[0]['project'] . '/');
		return;
	}
	$project = $_GET['project'];
	$project_id = _Project::get()->retrieve(array('project_id', 'default_page', 'project_start',
		'project_end'), array(), array('project' => $project));
	if (empty($project_id)) {
		header('Location: /zazz/index.php');
		return;
	}
	$default_page_id = $project_id[0]['default_page'];
	$project_start = $project_id[0]['project_start'];
	$project_end = $project_id[0]['project_end'];
	$project_id = $project_id[0]['project_id'];
	$default_page = _Page::get()->retrieve('page', array(), array('page_id' => $default_page_id));
	if (empty($default_page[0]['page'])) {
		echo 'There has been a serious error.';
		return;
	}
	$default_page = $default_page[0]['page'];
	_User::get()->update(array('active_project' => $project_id), array('user_id' => $user_id));

	if (!isset($_GET['page']) || empty($_GET['page'])) {
		header('Location: /zazz/build/' . $project . '/' . $default_page);
		return;
	}
	$page = $_GET['page'];

	$page_info = getPageInformation($project, $page);
	if (empty($page_info)) {
		$project = _Project::get()->retrieve('project', array(),
			array('project_id' =>
			Authenticate::get()->getUser('active_project')));
		header('Location: /zazz/build/' . $project[0]['project'] . '/');
		return;
	}
	$page_id = $page_info['page_id'];

	$page_id = intval($page_id);
	$project_start = intval($project_start);
	$project_end = intval($project_end);
	$query = Database::get()->PDO()->prepare("SELECT code FROM code WHERE type = 'html' AND page_id IN (" .
		"$page_id, $project_start, $project_end) AND zazz_id IN ('begin-project', 'end-project'," .
		"'begin-web-page','end-web-page') ORDER BY zazz_id, zazz_order");
	$query->execute();
	$code = $query->fetchAll(PDO::FETCH_COLUMN, 0);
	$frame = implode('', $code);

	require_once dirname(__FILE__) . '/includes/custom/simple_html_dom.php';
	$html = new simple_html_dom();
	$html->load($frame);
	if ($html->find('body', 0) && $html->find('head', 0)) {
		$bad_html = false;
		ob_start();
	} else {
		$bad_html = true;
	}
}
if ($bad_html) {
	?>
	<!DOCTYPE html>
	<html>
		<?php require_once dirname(__FILE__) . '/includes/custom/header.php'; ?>
		<body>
			<?php
		}
		?>
		<div id="-zazz-modal-alert" class="-zazz-modal">
			<div class="-zazz-modal-header">Oops...</div>
			<div class="-zazz-modal-body"></div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-modal-confirm" class="-zazz-modal">
			<div class="-zazz-modal-header">Confirm</div>
			<div class="-zazz-modal-body">Are you sure you want to do this?</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Cancel" />
				<input type="button" class="-zazz-modal-button" value="Continue" />
			</div>
		</div>
		<div id="-zazz-modal-deploy-confirm" class="-zazz-modal">
			<div class="-zazz-modal-header">Confirm</div>
			<div class="-zazz-modal-body">Deploying the project will make all visible pages visible 
				at the root URL. If you want to do this, enter the deploy password.
				<table>
					<tr>
						<td>Deploy Password: </td>
						<td><input id="-zazz-deploy-password" type="text" /></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Cancel" />
				<a id='-zazz-deploy-link'class="-zazz-modal-button -zazz-modal-close" 
					 href="/zazz/view/<?= $project ?>/<?= $page ?>?deploy=true" target="_blank">Continue</a>
			</div>
		</div>
		<div id="-zazz-modal-settings" class="-zazz-modal">
			<div class="-zazz-modal-header">Page</div>
			<div class="-zazz-modal-body">
				<p class="-zazz-modal-message"></p>
				<table>
					<tr>
						<td>Page Name: </td>
						<td><input id="-zazz-page-name" type="text" value="<?= $page ?>" /></td>
					</tr>
					<tr>
						<td>Visible When Deployed: </td>
						<td><select id='-zazz-page-visible'>
								<option>Yes</option>
								<option <?= ($page_info['visible'] ===
		'0' ? 'selected' : '')
		?>>No</option>
							</select></td>						
					</tr>
					<tr>
						<td>Page Height: </td>
						<td><input id="-zazz-page-height" type="text" /></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-dropdown-project" class="-zazz-dropdown">
			<span tabindex="10" id="-zazz-edit-project-btn" class="-zazz-btn">Edit</span
			><span tabindex="10" id="-zazz-new-project-btn" class="-zazz-btn">New</span
			><span tabindex="10" id="-zazz-switch-project-btn" class="-zazz-btn">Switch</span
			><span tabindex="10" id="-zazz-delete-project-btn" class="-zazz-btn">Delete</span
			>
		</div>
		<div id="-zazz-dropdown-page" class="-zazz-dropdown">
			<span tabindex="10" id="-zazz-edit-page-btn" class="-zazz-btn">Edit</span
			><span tabindex="10" id="-zazz-new-page-btn" class="-zazz-btn">New</span
			><span tabindex="10" id="-zazz-switch-page-btn" class="-zazz-btn">Switch</span
			><span tabindex="10" id="-zazz-delete-page-btn" class="-zazz-btn">Delete</span
			>
		</div>
		<div id="-zazz-modal-view-projects" class="-zazz-modal">
			<div class="-zazz-modal-header">All Projects</div>
			<div class="-zazz-modal-body">
				<table class="-zazz-links">
					<?php
					if (!$demo) {
						$viewProjects = _Project::get()->retrieve(array('project'), array(),
							array('user_id' => $user_id));
						$hasProject = false;
						foreach ($viewProjects as $viewProject) {
							if ($viewProject['project'] !== $project) {
								$hasProject = true;
								echo '<tr><td><a href="/zazz/build/' . $viewProject['project'] . '/">'
								. $viewProject['project'] . '</a></tr></td>' . "\n";
							}
						}
						if (!$hasProject) {
							echo '<tr><td>You have only one project.</td></tr>';
						}
					} else {
						echo '<tr><td>You can only have one project in the demo.</td></tr>';
					}
					?>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-modal-view-pages" class="-zazz-modal">
			<div class="-zazz-modal-header">All Pages</div>
			<div class="-zazz-modal-body">
				<table class="-zazz-links">
					<?php
					if (!$demo) {
						$viewPages = _Page::get()->retrieve(array('Page'), new Join('project_id', _Project::get()),
							array('project' => $project, 'user_id' => $user_id));
						$hasPage = false;
						foreach ($viewPages as $viewPage) {
							if ($viewPage['page'] !== 'begin-project' && $viewPage['page'] !== 'end-project'
								&& $viewPage['page'] !== $page) {
								$hasPage = true;
								echo '<tr><td><a href="/zazz/build/' . $project . '/' . $viewPage['page'] . '">'
								. $viewPage['page'] . '</a></tr></td>' . "\n";
							}
						}
						if (!$hasPage) {
							echo '<tr><td>You only have one page.</td></tr>';
						}
					} else {
						echo '<tr><td>You can only have one page in the demo.</td></tr>';
					}
					?>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-modal-upload" class="-zazz-modal">
			<div class="-zazz-modal-header">Upload</div>
			<div class="-zazz-modal-body">
				<p class="-zazz-modal-message"></p>
				<table>
					<tr>
						<td>Filename:</td>
						<td><input id="-zazz-upload-filename" type="text" /></td>
					</tr>
					<tr>
						<td>Filename on Server:</td>
						<td><input id="-zazz-upload-server" type="text" /></td>
					</tr>
				</table>
				<form id='-zazz-upload-form' action="/zazz/ajax/project.php" method="post" 
							enctype="multipart/form-data"	style="display:none" target="-zazz-uploaded-result">
					<input type="text" name="upload_name" id="-zazz-upload-name" />
					<input type='text' name='page_id' id='-zazz-upload-page-id' />
					<input type="file" name="upload" id="-zazz-upload-file" />
				</form>
				<p id="-zazz-uploaded-files"></p>
				<iframe src='/zazz/ajax/project.php?page_id=<?= $page_id ?>&files=true' 
								name="-zazz-uploaded-result" frameBorder="0"></iframe>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
				<input id='-zazz-upload-do-it' type="button" class="-zazz-modal-button" value="Upload" />
			</div>
		</div>		
		<div id="-zazz-modal-project" class="-zazz-modal">
			<div class="-zazz-modal-header">Project</div>
			<div class="-zazz-modal-body">
				<p class="-zazz-modal-message"></p>
				<table>
					<tr>
						<td>Project Name:</td>
						<td><input id="-zazz-project-name" type="text" value="<?= $project ?>"/></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-modal-new-project" class="-zazz-modal">
			<div class="-zazz-modal-header">New Project</div>
			<div class="-zazz-modal-body">
				<p class="-zazz-modal-message"></p>
				<table>
					<tr>
						<td>Project Name:</td>
						<td><input id="-zazz-new-project-name" type="text"/></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Cancel" />
				<input type="button" id="-zazz-make-new-project" value="Continue" />
			</div>
		</div>
		<div id="-zazz-modal-new-page" class="-zazz-modal">
			<div class="-zazz-modal-header">New Page</div>
			<div class="-zazz-modal-body">
				<p class="-zazz-modal-message"></p>
				<table>
					<tr>
						<td>Page Name:</td>
						<td><input id="-zazz-new-page-name" type="text"/></td>
					</tr>
					<tr>
						<td>Template:</td>
						<td><select id="-zazz-page-template"><option></option><?php
								foreach ($viewPages as $viewPage) {
									if ($viewPage['page'] !== 'begin-project' && $viewPage['page'] !== 'end-project') {
										echo '<option>' . $viewPage['page'] . '</option>';
									}
								}
								?></select></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Cancel" />
				<input type="button" id="-zazz-make-new-page" value="Continue" />
			</div>
		</div>
		<div id="-zazz-modal-mouse" class="-zazz-modal">
			<div class="-zazz-modal-body">
				<p class="-zazz-modal-message"></p>
				<table>
					<tr id='-zazz-modal-mouse-offset'>
					</tr>
					<tr id='-zazz-modal-mouse-location'>
					</tr>
					<tr id='-zazz-modal-mouse-offsetp'>
					</tr>
					<tr id='-zazz-modal-mouse-locationp'>
					</tr>
				</table>
				<p> (Top, Left, Bottom, Right) </p>
			</div>
		</div>
		<div class="-zazz-horizontal-line-left -zazz-line"> </div>
		<div class="-zazz-horizontal-line-right -zazz-line"> </div>
		<div class="-zazz-vertical-line-top -zazz-line"> </div>
		<div class="-zazz-vertical-line-bottom -zazz-line"> </div>
		<div class="-zazz-view">
			<div class="-zazz-navbar">
				<span class="-zazz-btn-group">
					<span tabindex="10" class="-zazz-select-btn -zazz-btn">Select</span
					><span tabindex="10" class="-zazz-vertical-btn -zazz-btn">Vertical</span
					><span tabindex="10" class="-zazz-across-btn -zazz-btn">Across</span
					><span tabindex="10" class="-zazz-absorb-btn -zazz-btn">Absorb</span
					><span class="-zazz-divider"></span
					><span id="-zazz-fixed"><span class="-zazz-fixed-btn">Fixed:</span
						><select id="-zazz-fixed-vertical"
										 ><option>None</option
							><option>Left</option
							><option>Right</option
							><option>Both</option
							></select
						><span class="-zazz-divider"></span></span>
				</span>
				<span class="-zazz-btn-group -zazz-set-right">
					<span id='-zazz-loader-bar'><span class="-zazz-divider"></span
					><img id='-zazz-loader-image' src='/zazz/css/images/loader.gif' /></span
					><span class="-zazz-divider"></span
					><span tabindex="10" class="-zazz-upload-btn -zazz-btn">Upload</span
					><span class="-zazz-divider"></span
					><!--<span tabindex="10" class="-zazz-save-all-btn -zazz-btn">Layer</span
					>--><span tabindex="10" class="-zazz-page-btn -zazz-btn">Page</span
					><span tabindex="10" class="-zazz-project-btn -zazz-btn">Project</span
					><span class="-zazz-divider"></span
					><span tabindex="10" class="-zazz-view-btn -zazz-btn"
								 ><a href="/zazz/view/<?= $project ?>/<?= $page ?>" target="_blank">View</a></span
					><span tabindex="10" id="-zazz-deploy-project-btn" class="-zazz-btn">Deploy</span
					><span tabindex="10" id="-zazz-export-btn" class="-zazz-btn"
								 ><a href="/zazz/view/<?= $project ?>/zip/<?= $project ?>.zip?export=true" 
							target="_blank">Export</a></span
					><span class="-zazz-divider"></span
				</span>
			</div>
			<div class="-zazz-content-view">
				<?php
				if (!$demo) {
					echo getComputedLayout($project_start, $project_end, $page_id);
				} else {
					echo getDefaultLayout();
				}
				?>
			</div>
		</div>
		<div class="-zazz-code-area">
			<div class="-zazz-navbar">
				<span class="-zazz-btn-group">
					<span class="-zazz-id-btn">ID:</span>
					<input tabindex="10" type="text" class="-zazz-id-input" 
								 /><span class="-zazz-class-btn">Class(es):</span>
					<input tabindex="10" type="text" class="-zazz-class-input" 
								 /><span class="-zazz-divider"></span
					><span id="-zazz-fixed-status-btn"
								 ><span class="-zazz-fixed-status-btn">Fixed</span><span class="-zazz-divider"></span
						></span></span>
				<span class="-zazz-btn-group -zazz-set-right">
					<span class="-zazz-divider"></span
					><span tabindex="10" class="-zazz-html-btn -zazz-btn">HTML</span
					><span tabindex="10" class="-zazz-js-btn -zazz-btn">JS</span
					><span tabindex="10" class="-zazz-php-btn -zazz-btn">PHP</span
					><span tabindex="10" class="-zazz-mysql-btn -zazz-btn">MySQL</span
					><span class="-zazz-divider"><!--</span
					><span tabindex="10" class="-zazz-import-btn -zazz-btn">Import</span
					><span tabindex="10" class="-zazz-export-btn -zazz-btn">Export</span>--></span>
			</div>
			<div class="-zazz-code-blocks">
				<?php
				if (!$demo) {
					getCodeBlocks($page_id, $project_start, $project_end);
				} else {
					getDefaultCodeBlock();
				}
				?>
			</div>
		</div>
		<input id="-zazz-page-id" type="hidden" value="<?= $page_id ?>" />
		<input id="-zazz-is-demo" type="hidden" value="<?= $demo ?>" />
		<input id="-zazz-bad-html" type="hidden" value="<?= $bad_html ?>" />
		<?php
		if ($bad_html) {
			?>
		</body>
		<script src="/zazz/js/jquery-1.10.2.js" type="text/javascript"></script>
		<script src="/zazz/js/functions.js" type="text/javascript"></script>
	</html>
	<?php
}
if (!$demo && !$bad_html) {
	echo $code[0] . $code[1] . ob_get_clean() . $code[2] . $code[3];
}
?>