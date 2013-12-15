<?php
require_once dirname(__FILE__) . '/Object.php';
require_once dirname(__FILE__) . '/Database.php';
require_once dirname(__FILE__) . '/Authenticate.php';
//Included below in the constructor.
//require_once dirname(__FILE__) . '/auto/_Log.php';

/**
 * A simple Logger class.
 */
class Logger extends Object {

	const LOG_ERRORS = true;
	const LOGGER_TABLE = 'log';
	const LOGGER_CLASS = '_Log';
	const ID_FIELD = 'log_id';
	const PRIORITY_FIELD = 'priority';
	const DATE_FIELD = 'date';
	const MESSAGE_FIELD = 'message';
	const ADDRESS_FIELD = 'address';
	const USER_ID_FIELD = 'user_id';
	const CRASH = 15;
	const HIGH = 12;
	const MEDIUM = 8;
	const LOW = 4;
	const INFO = 0;

	/**
	 *
	 * @var _Log $logger
	 */
	private $logger;
	private $ignore;
	private $message;
	
	/**
	 * Constructs an instance of the Logger and sets the appropiate error handlers to catch PHP 
	 * warnings, errors, and exceptions.
	 */
	protected function __construct() {
		require_once dirname(__FILE__) . '/auto/' . self::LOGGER_CLASS . '.php';		
		
		$loggerClass = self::LOGGER_CLASS;
		$this->logger = $loggerClass::get();
		$this->ignore = false;
		
		error_reporting(E_ALL);
		if(defined('DEVELOPER')) {
			ini_set('display_errors', 'On');
		}
		Database::get()->PDO()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if (self::LOG_ERRORS) {
			register_shutdown_function(array($this, "fatal_handler"));
			set_error_handler(array($this, "error_handler"));
		}
	}

	public function setIgnore($ignore) {
		$this->ignore = $ignore;
	}
	
	public function setMessage($message) {
		$this->message = $message;
	}
	
	public function fatal_handler() {
		$errfile = "unknown file";
		$errstr = "shutdown";
		$errno = E_CORE_ERROR;
		$errline = 0;

		$error = error_get_last();

		//If it is null, then there is no error. Just the script ending ("shutting down").
		if ($error !== NULL) {
			if(!$this->ignore) {
				$errno = $error["type"];
				$errfile = $error["file"];
				$errline = $error["line"];
				$errstr = $error["message"];

				$logger = Logger::get();
				if(strpos($errstr, "Uncaught exception 'PDOException'") !== false) {
					$query = CustomPDO::getLastQuery();
					$errstr = "Query:\n" . $query . "\nError:\n" . $errstr;
					if(defined('DEVELOPER')) {
						echo '<div style="background-color:#f57900;border: 1px solid black;padding:5px"> Query: ' . 
								$query . '</div>';
					}
				}
				$logger->log($errno . ": In " . $errfile . "\nOn line: " . $errline . ":\n" . $errstr,
						Logger::CRASH);
			}
			
			//Returning false passes the error along.
			if(defined('DEVELOPER') || $this->ignore) {
				return false;
			}
		}
	}

	public function error_handler($errno, $errstr, $errfile, $errline) {
		if(!$this->ignore) {
			Logger::get()->log($errno . ": In " . $errfile . "\nOn line: " . $errline . 
					":\n" . $errstr,
				Logger::CRASH);
		}
		
		//Returninf false passes the error along.
		if(defined('DEVELOPER') || $this->ignore) {
			if(!empty($this->message)) {
				echo $this->message;
				$this->message = '';
			}
			return false;
		}
	}

	/**
	 * Logs an error message into the database.
	 * @param string $message The message to be logged.
	 * @param int $priority A value representing the seriousness of the error.
	 */
	public function log($message, $priority = 4) {
		$auth = Authenticate::get();
		if ($auth->loggedIn()) {
			$user = $auth->getUser();
			$user_id = $user[Authenticate::ID];
		} else {
			$user_id = '0';
		}		

		try {
			$this->logger->create(array(self::MESSAGE_FIELD => $message, self::PRIORITY_FIELD => $priority, 
					self::USER_ID_FIELD => $user_id, self::ADDRESS_FIELD => $_SERVER['REMOTE_ADDR']));
		} catch(Exception $e) {
			print_r($e);
		}
	}

	/**
	 * Called to configure the database so that it has the appropiate table.
	 * @param Database $database
	 */
	public static function configure() {
		$q = Database::get()->PDO()->prepare('CREATE TABLE IF NOT EXISTS ' .
				self::LOGGER_TABLE . ' ( ' .
				self::ID_FIELD . ' INT PRIMARY KEY AUTO_INCREMENT, ' .
				self::USER_ID_FIELD . ' INT DEFAULT 0, ' .
				self::MESSAGE_FIELD . ' TEXT NOT NULL, ' .
				self::PRIORITY_FIELD . ' INT NOT NULL, ' .
				self::ADDRESS_FIELD . ' VARCHAR(20) NOT NULL, ' .
				self::DATE_FIELD . ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
			)');
		$q->execute();
	}

}

?>