<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package ReflectionAnnotate
 */
require_once 'CoreAnnotation.php';
require_once 'Annotation.php';

/**
 * Class representing class annotations.
 * 
 * @see ReflectionAnnotate_CoreAnnotation
 * @package ReflectionAnnotate
 */
class ReflectionAnnotate_ClassAnnotation extends ReflectionAnnotate_CoreAnnotation 
									     implements ReflectionAnnotate_Annotation {
	
	
	/**
	 * Checks for the presense of a class annotation.
	 *
	 * @param String $annotationName The name of the annotation
	 */
	public function isAnnotationPressent($annotationName, $className='') {
		$method = new ReflectionClass($this->className);
		return $this->matchAnnotationKey($annotationName, $method->getDocComment());
	}
	
	/**
	 * Get all the annotations for this class.
	 * 
	 * @todo This method should be implemented 
	 * @param String $annotationName The name of the annotation
	 * @return Array list of annotation names.
	 */
	public function getAnnotations($annotationName, $elementName='') {
		
	}
	
    /**
	 * Get all the annotations for this class.
	 * 
	 * @todo Support for multiple key value pairs. 
	 * @param String $annotationName The name of the annotation
	 * @return String Value of the annotation.
	 */
	public function getAnnotationValue($annotationName, $className='') {
		$method = new ReflectionClass($this->className);
		return $this->extractValueForAnnotation($annotationName, $method->getDocComment());
	}
}
?>