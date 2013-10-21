<?php
require_once dirname(__FILE__) . '/Database.php';
require_once dirname(__FILE__) . '/Object.php';
require_once dirname(__FILE__) . '/../functions.php';

//Included below in the constructor.
//require_once dirname(__FILE__) . '/auto/_Users.php';


class Authenticate extends Object {

	/**
	 * @var _Users $users
	 */
	private $userQB;
	private $userData;
	private $error = '';

	const SESSION = true;
	
	const USERS_CLASS = '_User';
	const USERS_TABLE = 'user';
	const ID = 'user_id';
	const USERNAME_FIELD = 'username';
	const PASSWORD_FIELD = 'password';
	const IP_FIELD = 'last_address';
	const TOKEN_FIELD = 'token';
	const LAST_LOGIN_FIELD = 'last_login';
	const LOGIN_ERROR_FIELD = 'login_error_count';
	const ACTIVE_FIELD = 'active';
	const FIRST_NAME_FIELD = 'first_name';
	const LAST_NAME_FIELD = 'last_name';
	const OTHER_ID = 'active_project';
	const MAX_LOGIN_COUNT = 10;
	const COOKIE_LIFETIME = 88600;
	const REDIRECT = '/zazz/login.php';

	//Arrays can't be const.
	static private $ERROR_MESSAGE = array(
		0 => 'You did not enter your email address and password.',
		1 => 'There is no user with that username.',
		2 => 'Your account has been blocked due to too many incorrect login attempts.',
		3 => 'The password you entered is incorrect. Please try again.',
		4 => 'There is already a user with that username.',
		5 => 'The account associated with that username has been inactivated.'
	);

	//This is a string that specifies blowfish was used to encrypt this and specifies the strength.

	const SALT_PREFIX = '$2y$08$';

	/**
	 * Securely computes a password hash.
	 * Example Usage:
	 * //On Registration:
	 * $hashed_original_password = blowfish($original_password, $strength);
	 * //On Login:
	 * if(crypt($login_password, $hashed_original_password) === $hashed_original_password) {
	 * 	//Password is right
	 * } else {
	 * 	//Password is wrong. 
	 * }
	 *
	 * @param string The string to compute the hash for.
	 * @param int The strength of the hashing. Limit of 99. 10 is reasonable.
	 * @return PDO Connection to the database
	 */
	static public function blowfish($password) {
		$strong = '';
		$salt = self::SALT_PREFIX . substr(str_replace('+', '.',
					base64_encode(openssl_random_pseudo_bytes(16, $strong))), 0, 22);

		//For more information about crypt, see the PHP manual.		
		return substr(crypt($password, $salt), 7);
	}

	/**
	 * setcookie doens't handle localhost in an intuitive manner, so this function will get the server
	 * name correctly for setcookie regardless. 
	 * @return type
	 */
	private static function getServerName() {
		if (!isLocal()) {
			$server = getServerName();
		} else {
			$server = false;
		}
		return $server;
	}

	/**
	 * Attemtps to get user information by authenticating the user based on cookie information. 
	 * Note this function updates cookies and last login time after retreiving and verifying the user.
	 * 
	 * @param boolean When set to false, will return null on failure rather than redirect.
	 * @return array The user as an array with column names used as keys.
	 */
	protected function __construct() {
		require_once dirname(__FILE__) . '/auto/' . self::USERS_CLASS . '.php';
		$usersClass = self::USERS_CLASS;
		$this->userQB = $usersClass::get();

		if ((self::SESSION && !isset($_SESSION['_thetoken'])) ||
			(!self::SESSION && !isset($_COOKIE['_thetoken']))) {
			$this->userData = null;
			return;
		}

		$token_hash = (self::SESSION ? $_SESSION['_thetoken'] : $_COOKIE['_thetoken']);
		$r = $this->userQB->retrieve(array(), array(),
			array(self::TOKEN_FIELD => $token_hash,
			self::ACTIVE_FIELD => '1'));

		if (count($r) === 0) {
			if(!self::SESSION) {
				setcookie('_thetoken', '', time() - 88000, '/', self::getServerName(), getHTTPS(), true);
			} else {
				unset($_SESSION['_thetoken']);
			}
			$this->userData = null;
			return;
		}

		$this->userQB->update(array(self::LAST_LOGIN_FIELD => date("Y-m-d H:i:s"),
			self::IP_FIELD => $_SERVER['REMOTE_ADDR'], self::LOGIN_ERROR_FIELD => '0'),
			array(self::TOKEN_FIELD, $token_hash));

		if(!self::SESSION) {
			$hour = time() + self::COOKIE_LIFETIME;
			setcookie('_thetoken', $token_hash, $hour, '/', self::getServerName(), getHTTPS(), true);
		} else {
			$_SESSION['_thetoken'] = $token_hash;
		}
		
		$this->userData = $r[0];
	}

	/**
	 * Checks to see if the user is logged in and if not, redirects the user. 
	 */
	public function check($redirect = true) {
		if (!$this->loggedIn()) {
			if ($redirect) {
				header("Location: " . self::REDIRECT);
			}
			exit();
		}
	}

	/**
	 * Checks to see if the user is logged in.
	 * @return boolean True if logged in, false otherwise.
	 */
	public function loggedIn() {
		if ($this->userData === NULL) {
			return false;
		}
		return true;
	}

	/**
	 * Logs the user out by resetting cookie information.
	 */
	public function logout() {
		$this->userData = null;
		if(self::SESSION) {
			unset($_SESSION['_thetoken']);
		} else {
			setcookie('_thetoken', '', time() - 18000, '/', self::getServerName(), getHTTPS(), true);
		}
	}

	/**
	 * Attempts to log in the user using provided username and password. If successful,
	 * $_COOKIE is set with the appropiate information, and true is returned. If unsuccessful,
	 * false is returned and an error message explaining why there was a failure is
	 * available by calling getError().
	 * 
	 * @param string Username (Can have extra whitespace)
	 * @param string Password (Can have extra whitespace)
	 * @return boolean True or false based of success of function
	 */
	public function login($username, $password) {
		$username = trim(strtolower($username));
		$password = trim($password);
		if (empty($username) || empty($password)) {
			$this->error = self::$ERROR_MESSAGE[0];
			return false;
		}

		$r = $this->userQB->retrieve(array(), array(), array(self::USERNAME_FIELD => $username));

		if (count($r) === 0) {
			$this->error = self::$ERROR_MESSAGE[1];
			return false;
		}

		if ($r[0][self::ACTIVE_FIELD] === '0') {
			$this->error = self::$ERROR_MESSAGE[5];
			return false;
		}

		$login_error_count = $r[0][self::LOGIN_ERROR_FIELD];
		$hashed_password = $r[0][self::PASSWORD_FIELD];
		if ($login_error_count > self::MAX_LOGIN_COUNT) {
			$this->error = self::$ERROR_MESSAGE[2];
			return false;
		}

		if (crypt($password, self::SALT_PREFIX . $hashed_password) !== self::SALT_PREFIX .
			$hashed_password) {
			$login_error_count++;
			$q = $this->userQB->update(array(self::LOGIN_ERROR_FIELD => $login_error_count),
				array(self::USERNAME_FIELD => $username));
			$this->error = self::$ERROR_MESSAGE[3];
			return false;
		}

		$token_hash = Authenticate::blowfish(microtime());

		$q = $this->userQB->update(array(self::TOKEN_FIELD => $token_hash, self::LAST_LOGIN_FIELD =>
			date("Y-m-d H:i:s"), self::IP_FIELD => $_SERVER['REMOTE_ADDR'], self::LOGIN_ERROR_FIELD
			=> '0'), array(self::USERNAME_FIELD => $username));

		if(!self::SESSION) {
			$hour = time() + self::COOKIE_LIFETIME;
			setcookie('_thetoken', $token_hash, $hour, '/', self::getServerName(), getHTTPS(), true);
		} else {
			$_SESSION['_thetoken'] = $token_hash;
		}
		
		$this->userData = $r[0];

		return true;
	}

	/**
	 * A convenience method that will automatically catch if there is a user with the same username
	 * and will encrypt the password before it is entered into the database. This method will also 
	 * automatically activate the user unless the field is already set in the the parameters.
	 * @param array $params Key-value pairs where the key is a column name in the user's table.
	 * @return boolean Returns true if successful.
	 */
	public function create($params) {
		try {
			$params[self::PASSWORD_FIELD] = Authenticate::blowfish($params[self::PASSWORD_FIELD]);
			if (!isset($params[self::ACTIVE_FIELD])) {
				$params[self::ACTIVE_FIELD] = '1';
			}
			$this->userQB->create($params);
		} catch (PDOException $e) {
			if ($e->getCode() === '23000') {
				$this->error = self::$ERROR_MESSAGE[4];
				return false;
			} else {
				throw $e;
			}
		}
		return true;
	}

	/**
	 * Gets any error message generated when login() was called.
	 */
	public function getError() {
		return $this->error;
	}

	public static function configure() {
		$strong = false;
		openssl_random_pseudo_bytes(16, $strong);
		if (!$strong) {
			throw new Exception('Your hardware or operating system does not support the
				generation of strong random numbers.');
		}
		if (intval(CRYPT_BLOWFISH) === 0) {
			throw new Exception('Blowfish encryption is not enabled. Upgrade the server or
				use a different encryption function.');
		}

		$q = Database::get()->PDO()->prepare('CREATE TABLE IF NOT EXISTS ' . PREFIX . self::USERS_TABLE .
			' ( ' .
			self::ID . ' INT PRIMARY KEY AUTO_INCREMENT, ' .
			self::USERNAME_FIELD . ' VARCHAR(20) NOT NULL, ' .
			self::PASSWORD_FIELD . ' VARCHAR(60) NOT NULL, ' .
			self::IP_FIELD . ' VARCHAR(25), ' .
			self::TOKEN_FIELD . ' VARCHAR(60), ' .
			self::FIRST_NAME_FIELD . ' VARCHAR(60), ' .
			self::LAST_NAME_FIELD . ' VARCHAR(60), ' .
			self::LAST_LOGIN_FIELD . ' TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ' .
			self::LOGIN_ERROR_FIELD . ' INT, ' .
			self::ACTIVE_FIELD . ' BIT DEFAULT 0, ' .
			self::OTHER_ID . ' INT
				);
				CREATE UNIQUE INDEX USERNAME ON ' . PREFIX . self::USERS_TABLE .
			' (' . self::USERNAME_FIELD . ');');
		$q->execute();
	}

	/**
	 * Gets the authenticated user data if it exists. If passed a parameter, then will attempt to 
	 * get the value of that attribute of the user data (thus, you can do getUser('id')).
	 * @param string $field
	 * @return array
	 */
	public function getUser($field = '') {
		if (empty($field)) {
			return $this->userData;
		}
		return $this->userData[$field];
	}

}

?>