<?php
/*
 * Created on Apr 24, 2008
 *
 * Read ini file with config info
 */
class RESTServiceConfig {
	// TODO refactor template + prefix to prevent duplication/mismatch
	public $idTemplate;
	public $idPrefix;
	public $adapterClass;
	public $serviceClass;
	public $applicationURL;
	public $resourceNamespace;
	public $resourceDirectory;
	public $resources = Array();
	public $adapterSection = Array();
	
	function RESTServiceConfig($file=null) {
		$ini_array = parse_ini_file($file?$file:"RESTService.ini", true);
		$this->adapterClass = $ini_array['context']['adapter-class'];
		$this->serviceClass = $ini_array['context']['service-class'];
		$this->applicationURL = $ini_array['resource']['application-url'];
		
		$this->resourceNamespace = $ini_array['resource']['resource-namespace'];
		$this->resourceDirectory = $_SERVER['DOCUMENT_ROOT'].
									DIRECTORY_SEPARATOR.$this->resourceNamespace;
		$this->_parseResources();
		
		$this->idTemplate = $ini_array['resource']['id-template'];
		$this->idPrefix = $ini_array['resource']['id-prefix'];
		$this->adapterSection = $ini_array['adapter'];
	}
	
	/*
	 * As of yet (php 5.2) doesn't support namespaces.
	 * To prevent class name clashes we use a poormans 
	 * form of namespaces <resource name>_MyClass.
	 * In our case resource name is also the name of 
	 * the directory containing the resources.
	 */
	private function _parseResources() {
		if (!is_dir($this->resourceDirectory))
			throw new RESTException(
				"Couldn't find resource directory: '".$this->resourceDirectory."'", 500);
		
		if ($dh = opendir($this->resourceDirectory)) {
			while (($file = readdir($dh)) !== false) {
				array_push($this->resources, strtolower($file));
			}
			$this->resources = preg_replace("/\..*$/","",$this->resources);
		} else {
			throw new RESTException(
				"Couldn't open resource directory ".$this->resourceDirectory, 500);
		}
	}
	
	/*	 * Returns an array of resource class names 
	 */
	public function getResources() {
		 return $this->_resources();
	}
	
	/*
	 * Return true when we have the requested resource
	 */
	public function haveResource($resourceName) {
		return in_array(strtolower($resourceName), $this->resources);
	}
}
?>
