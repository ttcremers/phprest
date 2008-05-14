<?php
/**
 * Class representing method annotations
 *
 * @author ttcremers@gmail.com 29/04/2008
 */
class ReflectionAnnotate_MethodAnnotation extends CoreAnnotation implements ReflectionAnnotate_Annotation {
	
	function isAnnotationPressent($methodName, $annotationName) {
		$method = new ReflectionMethod($this->_className, $methodName);
		return $this->matchAnnotationKey($annotationName, $method->getDocComment());
	}
	
	// TODO Implement this
	function getAnnotations($methodName, $annotationName) {}
	
	function getAnnotationsValue($methodName, $annotationName) {
		$method = new ReflectionMethod($this->_className, $methodName);
		return $this->getAnnotationValue($annotationName, $method->getDocComment());
	}
}
?>