<?php
/*
 * Created on Apr 24, 2008
 * 
 * You should never have to change anything in this code
 * if your sollution results in having to make changes here
 * your sollution is wrong (or the author made a mistake).
 * 
 * If you want to add functionality you probably want to extend 
 * the CoreService object.
 */
require_once 'Request.php';
require_once 'Response.php';
require_once 'RESTServiceConfig.php';
require_once 'RESTException.php';
require_once 'CoreUtil.php';

$requestContext = new Request();
$responseContext = new Response($requestContext);
try {
	$serviceConfig = new RESTServiceConfig();
	$contentAdapter = CoreUtil::loadCoreClass($serviceConfig->adapterClass);
	$service = CoreUtil::loadCoreClass($serviceConfig->serviceClass);
	
	// Initialised everything, now service the request
	$service->service($requestContext, $responseContext, $contentAdapter, $serviceConfig);
	
} catch (Exception $e) {
	$responseContext->statusCode = $e->getCode() ? $e->getCode() : 500;
	$responseContext->mimeType = 'text/plain';
	$responseContext->body = $e->getMessage()."\n\n".$e->getTraceAsString();
}
// All things done write out the response to the client
$responseContext->writeResponse();
?>
