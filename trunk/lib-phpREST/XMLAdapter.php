<?php
/*
 * Created on Apr 24, 2008
 */
require_once 'AdapterInterface.php';
require_once 'CoreXMLShift.php';

class XMLAdapter implements AdapterInterface {
	
	public function bodyRead(Request $request, RESTServiceConfig $serviceConfig) {
		try {
			$xmlShift = new CoreXMLShift();
			
			// Load the IDResolver if any
			$xmlIDResolverClass = $serviceConfig->adapterSection['xml-idresolver-class'];
			if ($xmlIDResolverClass) {
				$object = CoreUtil::loadCoreClass($xmlIDResolverClass);
				if (is_object($object))
					$xmlShift->setIDResolver($object);
			}
			return $xmlShift->unMarshall($request->body);
		} catch (Exception $e) {
			throw new RESTException($e->getMessage(), 500);
		}
		return null;
	}
	
	public function bodyWrite($contentObjectRep, Response $response, RESTServiceConfig $serviceConfig) {
		try {
			$classNamespace = $serviceConfig->adapterSection['xml-class-namespace'];
			$xmlShift = new CoreXMLShift($classNamespace ? $classNamespace : null);
			$response->body=$xmlShift->marshall($contentObjectRep);
			return true;
		} catch (XMLShiftException $e) {
			throw new RESTException($e->getMessage(), 500);
		}
		return false;
	}
}
?>
