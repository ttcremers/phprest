<?php
/**
 * Class representing class annotations
 *
 * @author ttcremers@gmail.com 29/04/2008
 */
require_once 'CoreAnnotation.php';
require_once 'Annotation.php';

class ReflectionAnnotate_ClassAnnotation extends ReflectionAnnotate_CoreAnnotation 
									     implements ReflectionAnnotate_Annotation {
	
	public function isAnnotationPressent($annotationName, $className='') {
		$method = new ReflectionClass($this->className);
		return $this->matchAnnotationKey($annotationName, $method->getDocComment());
	}
	
	// TODO Implement this
	public function getAnnotations($annotationName, $elementName='') {
		
	}
	
	public function getAnnotationValue($annotationName, $className='') {
		$method = new ReflectionClass($this->className);
		return $this->extractValueForAnnotation($annotationName, $method->getDocComment());
	}
}
?>