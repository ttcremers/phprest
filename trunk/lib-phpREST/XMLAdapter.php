<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */
require_once 'AdapterInterface.php';
require_once 'CoreXMLShift.php';

/**
 * An phpREST content Adapter to parse and write xml content bodys
 * @package phpREST
 */
class XMLAdapter implements AdapterInterface {

	public function bodyRead(Request $request, RESTServiceConfig $serviceConfig) {
		try {
			$xmlShift = new CoreXMLShift();
			$this->loadIdResolver($xmlShift, $serviceConfig);
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
			$this->loadIdResolver($xmlShift, $serviceConfig);
			$response->body=$xmlShift->marshall($contentObjectRep);
			return true;
		} catch (XMLShiftException $e) {
			throw new RESTException($e->getMessage(), 500);
		}
		return false;
	}

	private function loadIdResolver(CoreXMLShift $xmlShift, RESTServiceConfig $serviceConfig){
		// Load the IDResolver if any
		$xmlIDResolverClass = $serviceConfig->adapterSection['xml-idresolver-class'];
		if ($xmlIDResolverClass) {
			$object = CoreUtil::loadCoreClass($xmlIDResolverClass);
			if (is_object($object))
			$xmlShift->setIDResolver($object);
		}
	}
}
?>
