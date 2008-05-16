<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package phpREST-Skeleton
 */
require_once 'IDResolverInterface.php';
class TestIDResolver implements IDResolverInterface {
	public function resolve($id, $className) {
		$myObject = CoreUtil::loadCoreClass($className);
		$myObject->setID($id);
		return $myObject;
	}
}
?>