<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V 2008
 * @package XMLShift
 */
/**
 * IDResolverInterface when implementing your own id resolver it should implement this interface.
 * @package XMLShift
 * @example phpREST-Skeleton/TestIDResolver.php
 */
interface IDResolverInterface {
	/**
	 * Should return Object found with id
	 * 
	 * @param $id The id found in the xml
	 * @param string $className Can be any classname but usually the classname that was annotated  
	 * @return object 
	 */ 
	function resolve($id, $className);
}
?>