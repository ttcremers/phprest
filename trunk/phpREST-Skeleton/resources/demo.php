<?php
/*
 * Created on Apr 28, 2008
 * 
 * This is an example resource class.
 * 
 * Anatomy of an phpREST Resource
 * 
 * 1. Resource classes are made in filenames equal to the
 *    name of the resource (without the namespace).
 * 2. Resource classes use poor-mans namespaces 
 * 3. Resource classes always implement ALL the CRUD methods
 * 4. Resource classes inherit a couple of handy properties
 *    - $this->id, if there was an id in the url this is it
 *    - $this->request, for unexpected needs
 *    - $this->serviceConfig, access to the service configuration
 * 
 */
require_once 'CoreResource.php';
require_once 'demo.php';

class resources_Demo extends CoreResource {
	
	// HTTP POST
	public function create($object, Response $response) {
		$response->statusCode=200;
		$response->body="Hello, ".$object->name." your age is: ". $object->age. " and a friendly message of a relative object: ".$object->refs[0]->message;
	}
	
	// HTTP PUT
	public function update($object, Response $response) {}
	
	// HTTP GET 
	public function select(Response $response) {
		$response->statusCode=200;
		$demo = new Demo();
		$demo->name='Thomas Cremers';
		$demo->age=$this->id;
		return $demo;
	}
	
	// HTTP DELETE
	public function delete(Response $response) {}
}
?>
