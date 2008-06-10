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
 * 
 * //TODO rename URL in method to URI
 */
interface IDResolver {
	/**
	 * Should return Object of type $className with the given $id. 
	 *  
	 * @param $id The id found in the xml
	 * @param string $className Can be any classname but usually the classname that was annotated  
	 * @return object, or null if not found 
	 */ 
	function resolve($id, $className);
	
	/**
	 * Returns the ID for the given Object.
	 *
	 * @param object $object
	 * @return int, the ID for the given object
	 */
	function reverse($object);
	
	/**
	 * Should return Object found at the given URI 
	 * 
	 * @param string uri the URI to retrieve an object for.  
	 * @return object 
	 */ 
	function resolveURL($uri);
	
	/**
	 * Construct a URL to the given object
	 * 
	 * @param object $object the object to construct an URI to
	 * @return string the URL to the given object
	 */
	function constructURL($object);
}
?>