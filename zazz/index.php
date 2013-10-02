<?php

function getCodeBlocks($page_id) {
	$rows = _Code::get()->retrieve(array('zazz_id', 'code', 'type', 'zazz_order'), array(),
		array('page_id' => $page_id));
	foreach ($rows as $row) {
		echo '<textarea class="-zazz-code-block -zazz-' . $row["type"] . '-code 
							-zazz-code-block-' . $row['zazz_id'] . '" 
							spellcheck="false" tabindex="1" _zazz-order="' . $row['zazz_order'] . '" 
							style="display: none;" >' . $row['code'] . '</textarea>';
	}
}

function getLayout($page_id) {
	$result = _Layout::get()->retrieve('layout', array(), array('page_id' => $page_id));
	echo $result[0]['layout'];
}

function getPageID($project, $page) {
	$user_id = Authenticate::get()->getUser('user_id');
	$id = _Page::get()->retrieve('page_id', new Join('project_id', _Project::get()),
		array('project' => $project, 'page' => $page, 'user_id' => $user_id));
	return $id[0]['page_id'];
}

require_once dirname(__FILE__) . '/includes/standard/initialize.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';

Authenticate::get()->check();

if (!isset($_GET['project'])) {
	$project = _Project::get()->retrieve('project', array(),
		array('project_id' =>
		Authenticate::get()->getUser('active_project')));
	header('Location: /zazz/build/' . $project[0]['project'] . '/index.php');
	return;
}
$project = $_GET['project'];

if (!isset($_GET['page'])) {
	header('Location: /zazz/build/' . $project . '/index.php');
	return;
}
$page = $_GET['page'];

$page_id = getPageID($project, $page);
?>

<!DOCTYPE html>
<html>
	<?php require_once dirname(__FILE__) . '/includes/custom/header.php'; ?>
	<body>
		<div id="-zazz-modal-alert" class="-zazz-modal">
			<div class="-zazz-modal-header">Oops...</div>
			<div class="-zazz-modal-body"></div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-modal-settings" class="-zazz-modal">
			<div class="-zazz-modal-header">Settings</div>
			<div class="-zazz-modal-body">
				<table>
					<tr>
						<td>Page Name: </td>
						<td><input id="-zazz-page" type="text" value="<?= $page ?>" /></td>
					</tr>
					<tr>
						<td>Background Image: </td>
						<td><input id="-zazz-background-image" type="text" /></td>						
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div id="-zazz-modal-project" class="-zazz-modal">
			<div class="-zazz-modal-header">Project</div>
			<div class="-zazz-modal-body">
				<span>Project ID: </span><input id="-zazz-project-name" type="text" value="<?= $project ?>" />
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" value="Close" />
			</div>
		</div>
		<div class="-zazz-horizontal-line-left -zazz-line"> </div>
		<div class="-zazz-horizontal-line-right -zazz-line"> </div>
		<div class="-zazz-vertical-line-top -zazz-line"> </div>
		<div class="-zazz-vertical-line-bottom -zazz-line"> </div>
		<div class="-zazz-view">
			<div class="-zazz-navbar">
				<span class="-zazz-btn-group">
					<span tabindex="1" class="-zazz-select-btn -zazz-btn">Select</span
					><span tabindex="1" class="-zazz-vertical-btn -zazz-btn">Vertical</span
					><span tabindex="1" class="-zazz-across-btn -zazz-btn">Across</span
					><span tabindex="1" class="-zazz-absorb-btn -zazz-btn">Absorb</span
					><span class="-zazz-divider"></span>
				</span>
				<span class="-zazz-display -zazz-btn-group">
					<span class="-zazz-offset-btn -zazz-btn">Offset:</span
					><span class="-zazz-location-btn -zazz-btn">Location:</span>
				</span>
				<span class="-zazz-btn-group -zazz-set-right">
					<span class="-zazz-divider"></span
					><span tabindex="3" class="-zazz-project-btn -zazz-btn">Project</span
					><span tabindex="3" class="-zazz-settings-btn -zazz-btn">Settings</span
					><span tabindex="3" class="-zazz-save-all-btn -zazz-btn">Publish</span
					><span tabindex="3" class="-zazz-view-btn -zazz-btn">View</span>
				</span>
			</div>
			<div class="-zazz-content-view"><?php getLayout($page_id) ?></div>
		</div>
		<div class="-zazz-code-area">
			<div class="-zazz-navbar">
				<span class="-zazz-btn-group">
					<span class="-zazz-id-btn">ID:</span>
					<input tabindex="2" type="text" class="-zazz-id-input" />
					<span class="-zazz-class-btn">Class(es):</span>
					<input tabindex="2" type="text" class="-zazz-class-input" />
				</span>
				<span class="-zazz-divider"></span>
				<span class="-zazz-btn-group -zazz-set-right">
					<span class="-zazz-divider"></span
					><span tabindex="2" class="-zazz-html-btn -zazz-btn">HTML</span
					><span tabindex="2" class="-zazz-js-btn -zazz-btn">JS</span
					><span tabindex="2" class="-zazz-php-btn -zazz-btn">PHP</span
					><span tabindex="2" class="-zazz-mysql-btn -zazz-btn">MySQL</span
					><span class="-zazz-divider"></span
					><span tabindex="2" class="-zazz-import-btn -zazz-btn">Import</span
					><span tabindex="2" class="-zazz-export-btn -zazz-btn">Export</span></span>
			</div>
			<div class="-zazz-code-blocks"><?php getCodeBlocks($page_id) ?></div>
		</div>
		<input id="-zazz-page-id" type="hidden" value="<?= $page_id ?>" />
	</body>
	<script src="/zazz/js/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="/zazz/js/functions.js" type="text/javascript"></script>
</html>