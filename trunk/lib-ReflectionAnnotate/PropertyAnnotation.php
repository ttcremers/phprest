<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package ReflectionAnnotate
 */
require_once 'CoreAnnotation.php';
require_once 'Annotation.php';

/**
 * Class representing property annotations
 * @package ReflectionAnnotate
 */
class ReflectionAnnotate_PropertyAnnotation extends ReflectionAnnotate_CoreAnnotation 
											implements ReflectionAnnotate_Annotation {
	
	/**
	 * Checks for the precense of a property annotation.
	 *
	 * @param String $annotationName The name of the annotation
	 */										
	function isAnnotationPresent($annotationName, $propertyName='') {
		$property = new ReflectionProperty($this->className, $propertyName);
		return $this->matchAnnotationKey($annotationName, $property->getDocComment());
	}
	
	/**
	 * Get all the annotations for this method.
	 * 
	 * @todo This method should be implemented 
	 * @param String $annotationName The name of the annotation
	 * @return Array list of annotation names.
	 */
	function getAnnotations($annotationName, $propertyName='') {}
	
	/**
	 * Get all the annotations for this property.
	 * 
	 * @todo Support for multiple key value pairs. 
	 * @param String $annotationName The name of the annotation
	 * @return String Value of the annotation.
	 */
	function getAnnotationValue($annotationName, $propertyName='') {
		$property = new ReflectionProperty($this->className, $propertyName);
		return $this->extractValueForAnnotation($annotationName, $property->getDocComment());
	}
	
	/**
	 * Returns the property name of the first ocurance of $annotationName
	 *
	 * @param string $annotationName
	 * @return string name of the property
	 */
	function getPropertyWithAnnotation($annotationName) {
		$vars = get_object_vars($this->object);
		foreach ($vars as $propertyName => $propertyValue) {
			$property = new ReflectionProperty($this->className, $propertyName);
			if ($this->matchAnnotationKey($annotationName, $property->getDocComment()))
				return $propertyName;
		}
		return false;
	}
}
?>