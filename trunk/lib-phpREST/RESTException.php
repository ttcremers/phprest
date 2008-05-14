<?php
/*
 * Created on Apr 24, 2008
 */
class RESTException extends Exception {
	// Redefine the exception so message and code isn't optional
    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }
}
?>
