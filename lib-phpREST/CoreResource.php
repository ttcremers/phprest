<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

require_once 'ResourceInterface.php';
/**
 * It's the job of the Adapter to marshall and unmarshall content to 
 * object.
 * The setup method is called from CoreService to setup the resource object
 * 
 * @see AdapterInterface
 * @see CoreService
 * @package phpREST
 */
abstract class CoreResource implements ResourceInterface {
	abstract protected function create($object, Response $response);
	abstract protected function update($object, Response $response);
	abstract protected function select(Response $response);
	abstract protected function delete(Response $response);
	
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
