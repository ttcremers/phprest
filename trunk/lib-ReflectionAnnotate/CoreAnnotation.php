<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package ReflectionAnnotate
 */
require_once 'ReflectionAnnotateException.php';

/**
 * Base class for annotation types (method/property/class)
 * @package ReflectionAnnotate
 */
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
	
	/**
	 * Extract the annotation key out of the comment
	 * 
	 * @param string $key Name of the annotation
	 * @param string $comment Comment block which contains the annotation 
	 * @return boolean
	 */ 
	protected function matchAnnotationKey($key, $comment) {
		$regex = "/".constant('ReflectionAnnotate_CoreAnnotation::TAG_PREFIX').$key."(\s|:|$)/";
    	return preg_match($regex, $comment);
	}
	
	/**
	 * Get the annotations value
	 *
	 * @todo Built support for multiple key value pairs 
	 * @param string $key Name of the annotation
	 * @param string $comment Comment block which contains the annotation
	 * @return string Value of the annotation
	 */
	protected function extractValueForAnnotation($key, $comment) {
		$matches = array();
		$regex = "/".constant('ReflectionAnnotate_CoreAnnotation::TAG_PREFIX').$key.":\s*(\S*)(\r\n|\r|\n)/U";
		preg_match($regex, $comment, $matches);
		if ($matches[1]) 
			return trim($matches[1]);
	}
}
?>