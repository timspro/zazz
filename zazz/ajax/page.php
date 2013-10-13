<?php

require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check();
if(!verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'))) {
	return;
}

if (isset($_REQUEST['page']) && isset($_REQUEST['page_id'])) {
	_Page::get()->update($_REQUEST, array('page_id' => $_REQUEST['page_id']));
}

if (isset($_REQUEST['create']) && isset($_REQUEST['page_id'])) {
	if(empty($_REQUEST['create']) || ctype_space($_REQUEST['create'])) {
		echo 'Must have a page name.';
		return;
	}
	
	$result = _Page::get()->retrieve('project_id', array(), array('page_id' => $_REQUEST['page_id']));
	$page_id = _Page::get()->create(array('page' => $_REQUEST['create'], 
		'project_id' => $result[0]['project_id']));
	_Code::get()->create(array('zazz_id' => 'element-0', 'page_id' => $page_id, 'type' => 'css',
		'code' => "#element-0 {\n\n}", 'zazz_order' => '0'));

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
					id="element-0" _zazz-order="1" tabindex="1" class="-zazz-element" _zazz-id="element-0"></div
				></div
			></div
		></div>
	<?php
	$layout = ob_get_clean();
	_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout));
}

if (isset($_REQUEST['delete']) && isset($_REQUEST['page_id'])) {
	_Page::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Code::get()->delete(array('page_id' => $_REQUEST['page_id']));
	_Layout::get()->delete(array('page_id' => $_REQUEST['page_id']));
}

?>