<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V.
 * @package phpREST
 */

/**
 * @package phpREST
 */
class RESTException extends Exception {
	/**
	 * overwrite the exception contstructer so message and code isn't optional
	 */ 
    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }
}
?>
