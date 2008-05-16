<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST-Skeleton 
 */
require_once 'CoreResource.php';
require_once 'demo.php';

/** 
 * This is an example resource class.
 * 
 * Anatomy of an phpREST Resource
 * 
 * 1. Resource classes are made in filenames equal to the
 *    name of the resource (without the namespace).
 * 2. Resource classes use poor-mans namespaces 
 * 3. Resource classes always implement ALL the CRUD methods
 * 4. Resource classes inherit a couple of handy properties
 *    $this->id if there was an id in the url this is it,
 *    $this->request for unexpected needs,
 *    $this->serviceConfig access to the service configuration
 * 
 * @package phpREST-Skeleton
 * @example phpREST-Skeleton/resources/demo.php
 * @todo The Response object and the generic object should be class properties 
 */
class resources_Demo extends CoreResource {
	
	/**
	 * Gets called on a HTTP POST request on the resource
	 *
	 * @param object $object Object associated with this resource. Initialised by the adapter
	 * @param Response $response 
	 * @see lib-phpREST::AdapterInterface
	 */
	public function create($object, Response $response) {
		$response->statusCode=200;
		$response->body="Hello, ".$object->name." your age is: ". $object->age. " and a friendly message of a relative object: ".$object->refs[0]->message;
	}
	
	/**
	 * Gets called on a HTTP PUT request on this resource
	 *
	 * @param object $object Object associated with this resource. Initialised by the adapter
	 * @param Response $response 
	 */
	public function update($object, Response $response) {}
	
	/**
	 * Gets called on a HTTP GET request on this resource
	 *
	 * @param Response $response
	 * @return object Object to be marshalled to the right content-type by the adapter
	 * @see lib-phpREST::AdapterInterface
	 */ 
	public function select(Response $response) {
		$response->statusCode=200;
		$demo = new Demo();
		$demo->name='Thomas Cremers';
		$demo->age=$this->id;
		return $demo;
	}
	
	/**
	 * Gets called on a HTTP DELETE request on this resource.
	 *
	 * @param Response $response
	 */
	public function delete(Response $response) {}
}
?>
