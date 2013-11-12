<?php

require_once dirname(__FILE__) . '/initialize.php';

if(defined('DEVELOPER')) {
//	$pdo = Database::get()->PDO();
//	$q = $pdo->prepare('SHOW TABLES');
//	$q->execute();	
//	$rows = $q->fetchAll(PDO::FETCH_COLUMN, 0);
//	
//	foreach($rows as $row) {
//		$q = $pdo->prepare('DELETE FROM ' . $row);
//		$q->execute();			
//	}
	
	$pdo = Database::get()->PDO();
	$q = $pdo->prepare('SHOW DATABASES');
	$q->execute();	
	$rows = $q->fetchAll(PDO::FETCH_COLUMN, 0);	
	
	foreach($rows as $row) {
		if($row !== 'mysql' && $row !== 'performance_schema' && $row !== 'information_schema' 
			&& $row !== 'test') {
			$q = $pdo->prepare('DROP DATABASE ' . $row);
			$q->execute();
		}
	}
	
	$pdo = Database::get()->PDO();
	$q = $pdo->prepare('SELECT user FROM mysql.user');
	$q->execute();	
	$rows = $q->fetchAll(PDO::FETCH_COLUMN, 0);	
	
	foreach($rows as $row) {
		if($row !== 'root') {
			$q = $pdo->prepare('DELETE FROM mysql.user WHERE user = \'' . $row . '\'');
			$q->execute();
		}
	}	
	
	echo 'Delete completed. <br>';
} else {
	echo 'The constant DEVELOPER must be defined.';
}

?>