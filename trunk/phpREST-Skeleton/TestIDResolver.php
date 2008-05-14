<?php
require_once 'IDResolverInterface.php';
class TestIDResolver implements IDResolverInterface {
	public function resolve($id, $className) {
		$myObject = CoreUtil::loadCoreClass($className);
		$myObject->setID($id);
		return $myObject;
	}
}
?>