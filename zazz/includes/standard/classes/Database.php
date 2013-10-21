<?php
require_once dirname(__FILE__) . '/Object.php';

/**
 * A wrapper class that allows the last query to be retrieved for debugging. Shouldn't affect 
 * functionality in other ways.
 */
class CustomPDO extends PDO {
	static private $lastQuery;
	public function prepare($statement, $driver_options = array()) {
		self::$lastQuery = parent::prepare($statement, $driver_options);
		return self::$lastQuery;
	}
	static public function getLastQuery() {
		return self::$lastQuery->queryString;
	}
}

/**
 * A wrapper database class for a PDO instance.
 */
class Database extends Object {
	/**
	 * The PDO instance.
	 * @var PDO
	 */
	private $pdo;
	
	/**
	 * Constructs an instance of the DB class.
	 * @param string $server Server
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $database Database
	 */
	protected function __construct($server, $username, $password, $database = '') 
	{
		try {
			if(empty($database)) {
				$configure = 'mysql:host=' . $server;
			} else {
				$configure = 'mysql:host=' . $server . ';dbname=' .	$database;
			}
			$this->pdo = new CustomPDO($configure, $username, $password);
		} catch (PDOException $e) {
			//This could be logged but the logger requires a database connection and making a separate
			//file just to log this probably obvious error doesn't seem like a good idea.
			echo '<p> Could not connect to database. <br /> Error: ' . 
					$e->getMessage() . '</p>';
			die();
		}
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} 
	
	/**
	 * Gets the underlying PDO instance. This is useful because parent's get() will only return an 
	 * instance of the database class.
	 * @return PDO PDO
	 */
	public function PDO() { 
		return $this->pdo; 
	}
	
	/**
	 * Don't need to configure database.
	 */
	public static function configure() {}
}

?>