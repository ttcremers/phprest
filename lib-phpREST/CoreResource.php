<?php
/*
 * Created on Apr 24, 2008
 *
 * It's the job of the Adapter to marshall and unmarshall content to 
 * object.
 * 
 * The setup method is called from CoreService to setup the resource object
 */
require_once 'ResourceInterface.php';
abstract class CoreResource implements ResourceInterface {
	abstract protected function create($object, $response);
	abstract protected function update($object, $response);
	abstract protected function select($response);
	abstract protected function delete($response);
	
	protected $serviceConfig;
	protected $request;
	protected $id;
	
	public function setup(Request $request, RESTServiceConfig $serviceConfig) {
		$this->request=$request;
		$this->serviceConfig=$serviceConfig;
		// Parse the key patern from the config and do setup
		if (preg_match('/'.$serviceConfig->idTemplate.'$/', $request->url[1], $matches)) {
			// When a character grouping was used.
			if ($matches[1]) 
				$this->id = $matches[1];
			else 
				$this->id = $matches[0];
		}
	}
}
?>
