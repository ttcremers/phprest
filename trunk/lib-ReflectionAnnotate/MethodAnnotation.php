<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package ReflectionAnnotate
 */
/**
 * Class representing method annotations
 * @package ReflectionAnnotate
 */
class ReflectionAnnotate_MethodAnnotation extends CoreAnnotation implements ReflectionAnnotate_Annotation {
	
	/**
	 * Checks for the presence of a method annotation.
	 *
	 * @param String $annotationName The name of the annotation
	 */
	function isAnnotationPressent($methodName, $annotationName) {
		$method = new ReflectionMethod($this->_className, $methodName);
		return $this->matchAnnotationKey($annotationName, $method->getDocComment());
	}
	
	/**
	 * Get all the annotations for this method.
	 * 
	 * @todo This method should be implemented 
	 * @param String $annotationName The name of the annotation
	 * @return Array list of annotation names.
	 */
	function getAnnotations($methodName, $annotationName) {}
	
	/**
	 * Get all the annotations for this method.
	 * 
	 * @todo Support for multiple key value pairs. 
	 * @param String $annotationName The name of the annotation
	 * @return String Value of the annotation.
	 */
	function getAnnotationsValue($methodName, $annotationName) {
		$method = new ReflectionMethod($this->_className, $methodName);
		return $this->getAnnotationValue($annotationName, $method->getDocComment());
	}
}
?>