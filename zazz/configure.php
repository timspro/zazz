<?php

if(!extension_loaded('zip')) {
	echo 'ZIP extension not installed.';
} else {
	echo 'ZIP extension is installed.';
}

require_once dirname(__FILE__) . '/includes/standard/delete.php';
require_once dirname(__FILE__) . '/includes/standard/configure.php';

echo 'Completed.';

if(empty(error_get_last())) {
	unlink(__FILE__);
}

?>
