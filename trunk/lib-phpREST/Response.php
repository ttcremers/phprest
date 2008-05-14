<?php
/**
 * Created on Apr 24, 2008
 * 
 * @description Object representing the response.
 * 
 * TODO This class should implement an interface.
 */
class Response {
	public $mimeType;
	public $statusCode;
	public $statusMessage;
	public $headers;
	public $body;
	
	function Response($request) {
		$this->headers = array();
		// This is just an initial setting 
		$this->mimeType = $request->mimeType;
	}
	
	/**
	 * Write response to client
	 */
	public function writeResponse() {
		if(!isset($this->mimeType)){
			$this->mimeType = "text/plain";
		}
		
		// Set last-minute header information
		$this->setHeader("HTTP/1.0", $this->statusCode.' '.$this->statusMessage);
		$this->setHeader('Content-Type', $this->mimeType .'; charset=UTF-8');
		$this->setHeader('Content-Length', strlen($this->body));
		
		// Output headers
		foreach (array_keys($this->headers) as $name){
			header($name.": ".$headers[$name]);
		}

		// Output content
		if ($this->body)
			echo $this->body;
	}
	
	/**
	 * Sets a HTTP header
	 *
	 * @param string $header The name of the header to set
	 * @param string $value The value to set the header to
	 */
	public function setHeader($header, $value) {
		$headers[$header] = $value;
		header($header.": ".$value);		
	}

}
?>
