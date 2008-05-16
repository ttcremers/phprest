<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

/**
 * The interface that every phpREST adapter should implement
 * 
 * @package phpREST
 */
interface AdapterInterface {
	
	/**
	 * bodyRead is used to read the content body of the request and return an associated object.
	 * This object can be anything (also a string) and gets passed trough as an argument to the
	 * appropriat resource methode. The CoreService class is responsible for the routing of 
	 * HTTP methods, Adapter to Resource.
	 * 
	 * @see CoreService
	 * 
	 * @param Request $request 
	 * @param RESTserviceConfig $serviceConfig The phpREST config object
	 * @return object Should return a simple resource object
	 * 
	 */
	public function bodyRead(Request $request, RESTServiceConfig $serviceConfig);
	
	/**
	 * bodyRead is used to transform objects back to content. It gets passed the response object
	 * to set the body but also extra headers if needed.
	 * 
	 * @param object $object The object to be translate to content
	 * @param Response $response
	 * @param RESTServiceConfig $serviceConfig The phpREST config object
	 */
	public function bodyWrite($object, Response $response, RESTServiceConfig $serviceConfig);
}
?>
