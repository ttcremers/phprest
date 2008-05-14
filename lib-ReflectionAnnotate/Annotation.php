<?php
/**
 * This interface should be implemented by the classes
 * that represent the different elements of a class that
 * can be annotated.
 * 
 * @author ttcremers@gmail.com 29/04/2008
 */
interface ReflectionAnnotate_Annotation {
	
	/**
	 * Checks if annotation excists for the specified class 
	 * element.
	 *
	 * @param String $elementName
	 * @param String $annotationName
	 */
	function isAnnotationPressent($annotationName, $elementName='');
	
	/**
	 * Get all annotations for specified class element.
	 *
	 * @param String $elementName
	 * @param String $annotationName
	 */
	function getAnnotations($annotationName, $elementName='');
	
	/**
	 * Get the value of the specified annotion for the 
	 * specified class element.
	 *
	 * @param String $elementName
	 * @param String $annotationName
	 */
	function getAnnotationValue($annotationName, $elementName='');
}
?>