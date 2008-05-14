<?php
/*
 * Created on Apr 24, 2008
 *
 */
interface AdapterInterface {
	// Should return a simple resource object
	public function bodyRead(Request $request, RESTServiceConfig $serviceConfig);
	
	// Should setup the response object 
	public function bodyWrite($object, Response $response, RESTServiceConfig $serviceConfig);
}
?>
