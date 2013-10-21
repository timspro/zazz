<?php

require_once dirname(__FILE__) . '/initialize.php';

if(defined('DEVELOPER')) {
	$pdo = Database::get()->PDO();
	$q = $pdo->prepare('SHOW TABLES');
	$q->execute();	
	$rows = $q->fetchAll(PDO::FETCH_COLUMN, 0);
	
	foreach($rows as $row) {
		$q = $pdo->prepare('DELETE FROM ' . $row);
		$q->execute();			
	}
	
	echo 'Completed.';
} else {
	echo 'The constant DEVELOPER must be defined.';
}

?>