<?php
require_once 'XMLAdapter.php';

class XMLTidyAdapter extends XMLAdapter {
	public function bodyWrite($contentObjectRep, Response $response, RESTServiceConfig $serviceConfig){
		parent::bodyWrite($contentObjectRep, $response, $serviceConfig);
		
		$config = array(
					'indent' => true,
					'output-xml' => true,
					'input-xml' => true,
					'indent-attributes' => true);
		$tidy = new tidy();
		$tidy->parseString($response->body, $config, 'utf8');
		$tidy->cleanRepair();
		$response->body = $tidy;
	}
}
?>