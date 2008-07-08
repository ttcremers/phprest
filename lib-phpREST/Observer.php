<?php

interface Observer{
	// PHP doesn't have enums, or else $action would be one
	public function notify($action, $value);
}
?>