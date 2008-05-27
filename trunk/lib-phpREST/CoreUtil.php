<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

/**
 * Collection of static Util methods 
 * @package phpREST
 */
class CoreUtil {
	
	/**
	 * Loads and init a class
	 * 
	 * @static 
	 * @param string $className
	 * @return object
	 */
	static function loadCoreClass($className) {
		if (!class_exists($className)) {
			require_once $className.'.php';
		}
		return new $className();
	}

	/**
	 * Loads and inits a resource class
	 *
	 * @static 
	 * @param string $resourceNamespace
	 * @param string $resourceName
	 * @param string $resourceClassFile
	 * @return object
	 */
	static function loadResourceClass($resourceNamespace, $resourceName, $resourceClassFile) {
	    $className = $resourceNamespace.'_'.$resourceName;
		if (!class_exists($className)) {
			require_once $resourceClassFile.DIRECTORY_SEPARATOR.$resourceName.'.php';
			return new $className();
		}
	}
}
?>
