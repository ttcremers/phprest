<?php
/**
 * Class representing property annotations
 *
 * @author ttcremers@gmail.com 29/04/2008
 */
require_once 'CoreAnnotation.php';
require_once 'Annotation.php';

class ReflectionAnnotate_PropertyAnnotation extends ReflectionAnnotate_CoreAnnotation 
											implements ReflectionAnnotate_Annotation {
	
	function isAnnotationPressent($annotationName, $propertyName='') {
		$property = new ReflectionProperty($this->className, $propertyName);
		return $this->matchAnnotationKey($annotationName, $property->getDocComment());
	}
	
	// TODO Implement this
	function getAnnotations($annotationName, $propertyName='') {}
	
	function getAnnotationValue($annotationName, $propertyName='') {
		$property = new ReflectionProperty($this->className, $propertyName);
		return $this->extractValueForAnnotation($annotationName, $property->getDocComment());
	}
	
	/**
	 * Returns property name on the first ocurance of annotation name
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