<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package ReflectionAnnotate
 */

/**
 * Implementations of this interface represent the different elements of a class that
 * can be annotated.
 * @package ReflectionAnnotate
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