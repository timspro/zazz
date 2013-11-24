<?php
require_once dirname(__FILE__) . '/Database.php';

/**
 * Makes sure children classes of Object set up the database accordingly.
 * Called to initialize any necessary server-side functionality such as creating a table.
 * This can't be a regular abstract function because PHP doesn't require that children classes 
 * create a function that is also static for a static abstract function.
 */
interface Configurable {
	public static function configure();
}

/**
 * Object allows the first instance of a child class to be retrieved lazily via get(...). If the
 * instance has not yet been retrieved, then it will create an instance. Note that an instance created
 * by calling the constuctor of a child class is not stored.  If this functionality is desired, then
 * uncomment out the constructor, but be warned that this suggests an error in design because the point
 * of this class is to enforce an object being constructed only once. Calls to new Foo($a,$b) should be
 * replaced with Foo::get(array($a,$b)).
 */
abstract class Object implements Configurable {
	/**
	 * The store of child instantiations. Note that all children need to share the same store, which 
	 * is why it has protected scope rather than private.
	 */
	static protected $store; 

	/**
	 * Stores the child's instantiation. Child classes MUST call the parent constructor 
	 * (i.e. parent::__construct()) somewhere in its own constructor IF it does define a constructor.
	 * IF NOT, then the parent's constructor will automatically be called, so no worries.
	 */
	/*
	protected function __construct() {
		$class = get_called_class();
		if(!isset(self::$store[$class])) {
			self::$store[$class] = $this;
		}
	}
	*/
	
	/**
	 * Gets the first instance created of the child class, or creates it if it hasn't been created. 
	 * @param array $parameters Any parameters to pass to the constructor if it needs to be called.
	 * @return Object
	 */
	static public function get($parameters = array()) {
		$class = get_called_class();
		if(!isset(self::$store[$class])) {
			$parameterString = '';
			$i = 0;
			foreach($parameters as $parameter) {
				$var = 'parameter' . $i;
				$$var = $parameter;
				$parameterString .= '$' . $var . ',';
				$i++;
			}
			$parameterString = substr($parameterString, 0, strlen($parameterString) - 1);
			$eval = 'self::$store["' . $class . '"] = new ' . $class . '(' . $parameterString .');';
			eval($eval);
		}
		return self::$store[$class];
	}
}

?>