<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */
require_once 'ServiceInterface.php';
/**
 * CoreService routes request methods 
 * and calls the appropriate method on the content adapter.
 * @package phpREST
 */
class CoreService implements ServiceInterface {
	
	private $_contentAdapter=null;
	private $_request=null;
	private $_response=null;
	private $_resource=null;
	private $_serviceConfig=null;
	
	/**
	 * The service Method is called from the RESTProxy
	 * to service the current request.
	 * Based on the type of request it will call the appropriate 
	 * http type service method.
	 * 
	 * @todo add typing to this method
	 */
	public function service($request, $response, $contentAdapter, $serviceConfig) {
		$this->_contentAdapter = $contentAdapter;
		$this->_request = $request;
		$this->_response = $response;
		$this->_serviceConfig=$serviceConfig;
		$this->_loadResource();
								
		if ($request->method == "get")
			$this->get();
		else if ($request->method == "post")
			$this->post();
		else if ($request->method == "put")
			$this->put();
		else if ($request->method == "delete")
			$this->delete(); 
		else
			throw new RestException("Don't know how to service: ". $request->method, 405);			 
	}
	
	private function _loadResource() {
		$appURLLength = strlen(dirname($this->_serviceConfig->applicationURL));
		$url=null;
		if ($appURLLength > 1) 
			$url = substr($this->_request->url[1], $appURLLength);
		
		preg_match("/^\/(.*)\/|$/",$this->_request->url[1], $matches);
		$resourceName=$matches[1];
		if (!$resourceName)
			throw new RestException("Unable to extract resource name from URL: ". $this->_request->url[1], 500);
		
		// If resource exsists load it
		if ($this->_serviceConfig->haveResource($resourceName)) {
			$this->_resource = CoreUtil::loadResourceClass(
						$this->_serviceConfig->resourceNamespace, $resourceName,
						$this->_serviceConfig->resourceDirectory);
			// Setup the request
			$this->_resource->setup($this->_request, $this->_serviceConfig);
		} else {
			throw new RestException("Resource not found: ". $resourceName, 404);
		}
	}
	
	public function get() {
		$contentObjectRep = $this->_resource->select($this->_response);
		if(!$contentObjectRep) throw new RESTException("Could not find a resource at URL {$this->_request->fullUrl}",404);
		// Use the adapter to create the body representation of the object
		$resourceData = $this->_contentAdapter->bodyWrite(
							$contentObjectRep, $this->_response, $this->_serviceConfig);
	}
	
	public function post() {
		// Use the adapter to create the object representation of the content
		$contentObjectRep = $this->_contentAdapter->bodyRead($this->_request, $this->_serviceConfig);
		// Call the CRUD method on the resource 
		$this->_resource->create($contentObjectRep, $this->_response);
	}
	
	public function put() {
		// Use the adapter to create the object representation of the content
		$contentObjectRep = $this->_contentAdapter->bodyRead($this->_request, $this->_serviceConfig);
		// Call the CRUD method on the resource 
		$this->_resource->update($contentObjectRep, $this->_response);
	}
	
	public function delete() {
		$this->_resource->delete($this->_response);
	}
}
?>
