<?php
/*
 * Created on Apr 24, 2008
 */
interface ServiceInterface {
	public function service($request, $response, $contentAdapter, $serviceConfig);
	public function get();
	public function post();
	public function put();
	public function delete();
}
?>
