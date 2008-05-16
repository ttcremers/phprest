<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

/**
 * Interface for resources used internaly by CoreResource
 * @package phpREST
 */
interface ResourceInterface {
	function setup(Request $request, RESTServiceConfig $serviceConfig);
}
?>