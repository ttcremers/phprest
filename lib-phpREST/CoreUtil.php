<?php
/*
 * Created on Apr 24, 2008
 *
 * Collection of static Util methods 
 */
class CoreUtil {
	static function loadCoreClass($className) {
		if (!class_exists($className)) {
			require_once $className.'.php';
			return new $className();
		}
	}
	
	static function loadResourceClass($resourceNamespace, $resourceName, $resourceClassFile) {
	    $className = $resourceNamespace.'_'.$resourceName;
		if (!class_exists($className)) {
			require_once $resourceClassFile.DIRECTORY_SEPARATOR.$resourceName.'.php';
			return new $className();
		}
	}
}
?>
