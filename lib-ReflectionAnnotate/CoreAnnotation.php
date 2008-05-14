<?php
require_once 'ReflectionAnnotateException.php';
class ReflectionAnnotate_CoreAnnotation {
	
	const TAG_PREFIX='@';
	
	protected $className;
	protected $object;
	
	function __construct($object) {
		if (!$object) 
			throw new ReflectionAnnotateException("No object passed or null"); 
		$this->className = get_class($object);
		$this->object = $object;
	}
	
	// Extract the annotation key out of the comment
	protected function matchAnnotationKey($key, $comment) {
		$regex = "/".constant('ReflectionAnnotate_CoreAnnotation::TAG_PREFIX').$key."(\s|:|$)/";
    	return preg_match($regex, $comment);
	}
	
	// Get the annotation value for $key
	// TODO built support for multiple key value pairs
	protected function extractValueForAnnotation($key, $comment) {
		$matches = array();
		$regex = "/".constant('ReflectionAnnotate_CoreAnnotation::TAG_PREFIX').$key.":\s*(\w*)(\r\n|\r|\n)/U";
		preg_match($regex, $comment, $matches);
		if ($matches[1]) 
			return trim($matches[1]);
	}
}
?>