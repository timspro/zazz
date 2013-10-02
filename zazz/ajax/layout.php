<?php
require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/standard/classes/auto/_Layout.php';

if (isset($_REQUEST['layout']) && isset($_REQUEST['page_id'])) {
	_Layout::get()->create($_REQUEST, true);
}

?>
