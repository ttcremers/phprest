<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

/**
 * The service interface. If you need to implement your own custom Service object you need to implement this interface
 * @package phpREST
 */
interface ServiceInterface {
	public function service($request, $response, $contentAdapter, $serviceConfig);
	public function get();
	public function post();
	public function put();
	public function delete();
}
?>
