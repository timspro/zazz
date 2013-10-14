<?php

/*
 * This script will configure the database.
 */
	define('CONFIGURE', true);
	
	require_once dirname(__FILE__) . '/initialize.php';

	//Note that really ALL queries should operate under 'TRADITIONAL' (strict mode), so this should
	//be moved into Database.php if it can't be set in the .ini file.
	$pdo = Database::get()->PDO();
	$q = $pdo->prepare('CREATE DATABASE IF NOT EXISTS ' . DATABASENAME . '; USE ' . DATABASENAME . 
		'; SET sql_mode = "TRADITIONAL";');
	$q->execute();
	$q->closeCursor(); //Not sure why this is needed.
	
	$exclude = array('QueryBuilder.php', 'Object.php');
	try {
			$dir = dirname(__FILE__) . '/classes/';
			/* @var $item DirectoryIterator */
			foreach (new DirectoryIterator($dir) as $item) {
					if($item->isFile()) {
							$filename = $item->getFilename();
							if(in_array($filename, $exclude)) {
								continue;
							}
							include_once $dir . $filename;
							$classname = substr($filename, 0, strlen($filename) - 4);
							if(method_exists($classname, 'configure')) {
								$classname::configure();
							}
					}
			}

			$dir = dirname(__FILE__) . '/classes/auto/';
			/* @var $item DirectoryIterator */
			foreach (new DirectoryIterator($dir) as $item) {
					if($item->isFile()) {
							$filename = $item->getFilename();
							if(in_array($filename, $exclude)) {
								continue;
							}
							include_once $dir . $filename;
							$classname = substr($filename, 0, strlen($filename) - 4);
							if(method_exists($classname, 'configure')) {
								$classname::configure();
							}
					}
			}			
	} catch (Exception $e) {
			echo 'Exception: ' . $e->getMessage();
			die();
	}	
	
	$q = $pdo->prepare('SHOW TABLES');
	$q->execute();
	$tables = $q->fetchAll(PDO::FETCH_COLUMN, 0);
	
	foreach (new DirectoryIterator(dirname(__FILE__) . '/classes/auto/') as $item) {
		if(!$item->isDot()) {
			unlink(dirname(__FILE__) . '/classes/auto/' . $item->getFilename());
		}
	}
	
	$start = strlen(PREFIX);
	foreach($tables as $table) {
		$name = '_' . str_replace('','_',ucwords(str_replace('_', ' ', substr($table, $start))));
		$q = $pdo->prepare('SHOW COLUMNS FROM ' . $table);
		$q->execute();
		$columns = $q->fetchAll(PDO::FETCH_ASSOC);
		$columnsFixed = array();
		foreach($columns as $column) {
			$columnsFixed[$column['Field']] = $column; 
		}
		$columnsArray = var_export($columnsFixed, true);
		file_put_contents(dirname(__FILE__) . '/classes/auto/' . $name . '.php', '
<?php
require_once dirname(__FILE__) . "/../QueryBuilder.php";
class ' . $name . ' extends QueryBuilder {
public function getTable() { return "' . $table . '"; }
public function getColumns() { return 
' . $columnsArray . '; 
}
}
?>');
	}
	
	echo 'Completed.'
	
?>
