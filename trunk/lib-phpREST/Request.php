<?php
/*
 * Created on Apr 24, 2008
 */
class Request {
	public $method;
	public $mimeType;
	public $body;
	public $url;
	public $fullUrl;
	
	
	function Request() {
		$this->method = $this->_getHTTPMethod();
		$this->mimetype = $this->_getRequestBodyMimetype();
		$this->body = $this->_getRequestBody();
		$this->url = $this->_getURL();
		$this->fullUrl = $this->_getFullUrl();
	}
	
	private function _getFullUrl() {
		$host = $_SERVER['HTTP_HOST'];
		$url =  $this->_getURL();
		$fullUrl = "http://$host$url[1]";
		return $fullUrl;
	}
	
	private function _getURL() {
		$fullUrl = NULL;
		if (isset($_SERVER['REDIRECT_URL'])) {
            $fullUrl = $_SERVER['REDIRECT_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $fullUrl = $_SERVER['REQUEST_URI'];
        }
		$url = $fullUrl;
		if (isset($_SERVER['PHP_SELF'])) {
			$baseLength = strlen(dirname($_SERVER['PHP_SELF']));
			if ($baseLength > 1) {
				$url = substr($fullUrl, $baseLength);
			}
		}
		return array($fullUrl, $url);
	}
	
	private function _getHTTPMethod() {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			return strtolower($_SERVER['REQUEST_METHOD']);
		}
		return NULL;
	}

	private function _getRequestBodyMimetype(){
		if (isset($_SERVER['CONTENT_TYPE'])) {
			return $_SERVER['CONTENT_TYPE']; 
		} elseif (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
			return 'text/plain';
		}
		return NULL;
	}
	
	private function _getRequestBody() {
		if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
			$requestData = '';
			global $HTTP_RAW_POST_DATA;
			if (isset($HTTP_RAW_POST_DATA)) { // use the magic POST data global if it exists
				return $HTTP_RAW_POST_DATA;
			} else { // other methods
				$requestPointer = fopen('php://input', 'r');
				while ($data = fread($requestPointer, 1024)) {
					$requestData .= $data;
				}
				return $requestData;
			}
		}
		return NULL;
	}
}
?>
