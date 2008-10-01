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

	/**
	 * @var AdapterInterface
	 */
	private $_contentAdapter=NULL;

	/**
	 * @var Request
	 */
	private $_request=NULL;

	/**
	 * @var Response
	 */
	private $_response=NULL;

	/**
	 * @var ResourceInterface
	 */
	private $_resource=NULL;

	private $_resourceName;

	/**
	 * @var RESTServiceConfig
	 */
	private $_serviceConfig=NULL;

	private $_observers=NULL;
    
    private $_contentObjectRep=NULL;

	/**
	 * The service Method is called from the RESTProxy
	 * to service the current request.
	 * Based on the type of request it will call the appropriate
	 * http type service method.
	 *
	 * @todo add typing to this method
	 */
	public function service(Request $request, Response $response, AdapterInterface $contentAdapter, RESTServiceConfig $serviceConfig) {
		$this->_contentAdapter = $contentAdapter;
		$this->_request = $request;
		$this->_response = $response;
		$this->_serviceConfig=$serviceConfig;
		$this->_observers=$this->_loadObservers();
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

	private function _loadObservers(){
		$observers = array();
		foreach($this->_serviceConfig->observers as $observerClass){
			$observers[] = $this->_loadObserver($observerClass);
		}
		return $observers;
	}

	private function _loadObserver($className){
		if (!class_exists($className)) {
			require_once $className.'.php';
		}
		return new $className($this->_serviceConfig);
	}

	public function notifyObservers()
    {
		if (isset($this->_contentObjectRep) == true)
        {
            foreach($this->_observers as $observer){
                if ($this->_request->method == "post" && $this->_response->statusCode == 200)
                    $observer->notify("CREATE", $this->_contentObjectRep, $this->_resourceName);
		    }
        }
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

		$this->_resourceName = $resourceName;

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
		if(!$contentObjectRep && !$this->_response->statusCode) throw new RESTException("Could not find a resource at URL {$this->_request->fullUrl}",404);
		// Use the adapter to create the body representation of the object
		if($contentObjectRep) $resourceData = $this->_contentAdapter->bodyWrite(
							$contentObjectRep, $this->_response, $this->_serviceConfig);
	}

	public function post() {
		// Use the adapter to create the object representation of the content
		$this->_contentObjectRep = $this->_contentAdapter->bodyRead($this->_request, $this->_serviceConfig);
        // Call the CRUD method on the resource
		$this->_resource->create($this->_contentObjectRep, $this->_response);        
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
