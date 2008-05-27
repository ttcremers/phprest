<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V 2008
 * @package XMLShift
 */
/**
 * IDResolver when implementing your own id resolver it should implement this interface.
 * @package XMLShift
 * @example phpREST-Skeleton/TestIDResolver.php
 */
interface IDResolver {
	/**
	 * Should return Object found with id
	 * 
	 * @param $id The id found in the xml
	 * @param string $className Can be any classname but usually the classname that was annotated  
	 * @return object, or null if not found 
	 */ 
	function resolve($id, $className);
	
	/**
	 * Should return Object found with id
	 * 
	 * @param url the URL to retrieve an object for.  
	 * @return object 
	 */ 
	function resolveURL($url);
	
	/**
	 * Construct a URL to the given object
	 * 
	 * @param url the URL to retrieve an object for
	 * @return string the URL to the given object
	 */
	function constructURL($object);
}
?>