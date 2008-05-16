<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

/**
 * This is the entry point of you phpREST application.
 * 
 * Proxy with mod rewrite all request to this script and phpREST
 * will attempt to extract resource and id from your uri.
 * 
 * A good place to start with your phpREST application is the 
 * phpREST-Skeletion project. This PDT Eclipse project can be used
 * as a bases and contains everything you need to get started.
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
