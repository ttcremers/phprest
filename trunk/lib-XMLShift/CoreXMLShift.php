<?php
/**
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package XMLShift
 */
require_once 'ClassAnnotation.php';
require_once 'PropertyAnnotation.php';
require_once 'XMLShiftException.php';
/**
 * Provides functionality for marschalling and unmarshalling xml. 
 * 
 * Any object can be used as marshall/unmarshall object provided its public class properties
 * are anotated. For unmarshalling it also expects a setter method for the property.
 * @todo Access properties by there getter method
 * @todo Add an method to resolve one2one relationships (already has a test)
 * @todo spaghetti code in dire need of refactor
 * 
 * @example test/XMLShiftTest.php Multiple examples of how to use XMLShift 
 * @package XMLShift
 */
class CoreXMLShift {
	
	/**
	 * @var IDResolverInterface
	 */
	private $_idResolver;
	
	/**
	 * @param object $object XMLShift annotated object.
	 * @return String XML representation of the passed object. 
	 */
	function marshall($object) {
		try {
			$classAnno = new ReflectionAnnotate_ClassAnnotation($object);

			$xml = null;
			if ($classAnno->isAnnotationPresent("XmlRootElement")) {
				$rootNodeName = $classAnno->getAnnotationValue('XmlRootElement');
				$xml = new DOMDocument('1.0', 'UTF-8');
				$rootNode = $xml->appendChild($xml->createElement($rootNodeName));
				$propertyAnno = new ReflectionAnnotate_PropertyAnnotation($object);
				foreach (get_object_vars($object) as $key => $value) {

					// We need to call getter, variables may be there just for annotation
					$value = $this->callGetter($object,$key);
						
					// The XmlElement marker annotation superceeds all others
					if ($propertyAnno->isAnnotationPresent('XmlElement', $key)) {
						$element = $xml->createElement($key);
						if(!is_object($value)){
							$element->nodeValue = $value;
						} 
						$rootNode->appendChild($element);
						// Add an attribute direct to the main node
					} else if ($propertyAnno->isAnnotationPresent('XmlAttribute', $key) &&
								!$propertyAnno->isAnnotationPresent('XmlContainerElement', $key)) {
						$rootNode->setAttribute($key, $value);
							
						// Create container with attribute. XmlContainerElement is not a Marker
						// Annotation.
					} else if ($propertyAnno->isAnnotationPresent('XmlAttribute', $key) &&
								$propertyAnno->isAnnotationPresent('XmlContainerElement', $key)) {
						$containerName = $propertyAnno->getAnnotationValue('XmlContainerElement', $key);
						$element = $xml->createElement($containerName);
						$element->setAttribute($key, $value);
						$rootNode->appendChild($element);
					} else if ($propertyAnno->isAnnotationPresent('XmlRef', $key) &&
								!$propertyAnno->isAnnotationPresent('XmlContainerElement', $key)) {
						// TODO marshall XMLRef 						
					} else if ($propertyAnno->isAnnotationPresent('XmlRefLink', $key) && !is_null($value)){
						$parentNode = $xml->createElement($key);
						$parentNode->appendChild($this->createRefLinkElement($xml, $value));
						$rootNode->appendChild($parentNode);						
					} else if($propertyAnno->isAnnotationPresent('XmlRefLinkMany', $key) && !is_null($value)) {
						$parentNode = $xml->createElement($key);
						if (is_array($value)) {
							foreach ($value as $item) {
								$parentNode->appendChild($this->createRefLinkElement($xml, $item));
							}
						} else {
							throw new XMLShiftException("The XmlRefList annotation should annotate properties of type array");
						}
						$rootNode->appendChild($parentNode);
					}
				}
				
			} else {
				throw new XMLShiftException(
					"Object passed to marshaller isn't an XMLShift annotated class"
					);
			}
			return $xml->saveXML();
		} catch(Exception $e) {
			throw new XMLShiftException(
				"Error while marshalling xml: ". $e->getMessage()."\n\n".$e->getTraceAsString()
			);
		}
		return null;
	}

	/**
	 * @param mixed $xmlData can be a string with xml data or a DOM object 
	 * @param object $object reference object that is used to unmarshall the xml. If it's not given an attempted will be made to autoload one.
	 * @return Object represantation of the passed xml.
	 */
	function unMarshall($xmlData, $object=null) {
		$xml=null;		
		if ($xmlData instanceof DOMDocument) {
			$xml = $xmlData;
		} else {
			$xml = new DOMDocument('1.0','UTF-8');
			if (!$xml->loadXML($xmlData, LIBXML_NOBLANKS))
				throw new XMLShiftException("Error parsing xml");
			$xml->normalizeDocument();
		}
		// If object is null we try to look it up.
		if (!is_object($object)) {
			$object = $this->findObject($xml);
			if (!is_object($object)) {
				throw new XMLShiftException("Unable to find out to which object to marshall with");
			}
		}
		
		// Be careful with cases!
		$classAnno = new ReflectionAnnotate_ClassAnnotation($object);
		$propertyAnno = new ReflectionAnnotate_PropertyAnnotation($object);
		$elements = Array();
		foreach (get_object_vars($object) as $objectProperty => $value) {
			if ($propertyAnno->isAnnotationPresent('XmlElement', $objectProperty) &&
					!$propertyAnno->isAnnotationPresent('XmlContainerElement', $objectProperty)) {
				$node = $xml->getElementsByTagName($objectProperty)->item(0);
				$this->setObjectValue($node, $object, $objectProperty);
			
			} else {
				if ($propertyAnno->isAnnotationPresent('XmlContainerElement', $objectProperty)) {
					$containerElementName = $propertyAnno->getAnnotationValue('XmlContainerElement', $objectProperty);
					$container = $xml->getElementsByTagName($containerElementName)->item(0);
					if ($propertyAnno->isAnnotationPresent('XmlAttribute', $objectProperty)) {
						$attrNode = $container->getAttributeNode($objectProperty);
						$this->setObjectValue($attrNode, $object, $objectProperty);
					} else {
						$subElement = $container->getElementsByTagName($objectProperty)->item('0');
						$this->setObjectValue($subElement, $object, $objectProperty);	
					}
				} elseif ($propertyAnno->isAnnotationPresent('XmlRef', $objectProperty)) {
					$this->processXmlRef($xml->documentElement, $propertyAnno, $objectProperty, $object);
				} elseif ($propertyAnno->isAnnotationPresent('XmlRefLinkMany', $objectProperty)) {
					$this->lookupXmlRefList($xml->documentElement, $propertyAnno, $objectProperty, $object);
				} elseif ($propertyAnno->isAnnotationPresent('XmlRefLink', $objectProperty)) {
					$this->lookupXmlRefId($xml->documentElement, $propertyAnno, $objectProperty, $object);
				}
			} 
		}
		return $object;
	}
	
	/**
	 * Looks up an object based on the information in the xml. 
	 * The IDResolver is used to lookup the object with the id's found in the xml.
	 * @see IDResolverInterface
	 *
	 * @param DOMElement $node
	 * @param ReflectionAnnotate_PropertyAnnotation $propertyAnnotation
	 * @param string $objectProperty Name of the property where we set the object
	 * @param object $object to object on which we call the setter with the object
	 */
	protected function lookupXmlRefId(DOMElement $node, 
						ReflectionAnnotate_PropertyAnnotation $propertyAnnotation,
						$objectProperty, $object) {
		
		$xmlRefClass = $propertyAnnotation->getAnnotationValue('XmlRefLink', $objectProperty);
		
		$node = $node->getElementsByTagName($objectProperty)->item(0);
		$node = $node->getElementsByTagName('*')->item(0);
		$xmlId = $node->getAttribute('id');
		
		$resolvedObject = $this->_idResolver->resolve($xmlId, $xmlRefClass);
		
		$method = "set".ucFirst($objectProperty);
		$object->$method($resolvedObject);		
		
	}
	
	/**
	 * Looks up and creates a list of objects based on the information in the xml. 
	 * The IDResolver is used to lookup the objects with the id's found in the xml.
	 * @see IDResolverInterface
	 *
	 * @param DOMElement $node
	 * @param ReflectionAnnotate_PropertyAnnotation $propertyAnnotation
	 * @param string $objectProperty Name of the property where we set the array list of objects 
	 * @param object $object to object on which we call the setter with array list
	 */
	protected function lookupXmlRefList(DOMElement $node, 
						ReflectionAnnotate_PropertyAnnotation $propertyAnnotation,
						$objectProperty, $object) {
		$xmlRefClass = $propertyAnnotation->getAnnotationValue('XmlRefLinkMany', $objectProperty);

		$refNode = $node->getElementsByTagName($objectProperty)->item(0);
		$refChilderen = $refNode->childNodes;
		// NODEList class is not really a list so we need a for loop
		$objectList=Array();
		for ($i=0; $i<=$refChilderen->length; $i++) {
			if ($refChilderen->item($i)->tagName==strtolower($xmlRefClass)) {
				$itemAttributes = $refChilderen->item($i)->attributes;
				$xmlID = $itemAttributes->getNamedItem('id')->nodeValue;
				// Call the id resolver with id and class to resolve to object.
				array_push($objectList, $this->_idResolver->resolve($xmlID, $xmlRefClass));
			}
		}
		$method = "set".ucFirst($objectProperty);
		$object->$method($objectList);
	}
	
	/**
	 * Used to unmarshall an XMLShift object as child of an node
	 *
	 * @param DOMElement $node Node under which to marshall
	 * @param ReflectionAnnotate_PropertyAnnotation $propertyAnnotation
	 * @param string $objectProperty
	 * @param object $object
	 */
	protected function processXmlRef(DOMElement $node, 
						ReflectionAnnotate_PropertyAnnotation $propertyAnnotation,
						$objectProperty, $object) {
		$xmlRefClass = $propertyAnno->getAnnotationValue('XmlRef', $objectProperty);
		$refNode = $node->getElementsByTagName($objectProperty)->item(0);
		$xmlRefObject = $this->loadClass($xmlRefClass);
		
		// Create a new DOMDocument with which we can marshall
		$newDom = new DOMDocument('1.0', 'UTF-8');
		$newDom->appendChild($refNode);
		
		// Now unmarshall the node to object.
		$xmlRefObject = $this->unMarshall($newDom, $xmlRefObject);
		$method = "set".ucFirst($objectProperty);
		$object->$method($xmlRefObject);
	}
	
	/**
	 * Utilty method to set a given value on an XMLShift object
	 *
	 * @param DOMNode $node The node from which the value is extracted
	 * @param object $object the obect on which the setter is called
	 * @param string $property Name of property to set
	 */
	protected function setObjectValue($node, $object, $property) {
		$value = $node->nodeValue;
		$method = "set".ucFirst($property);
		$object->$method($value);
	}

	/**
	 * Utilty method that attempts to find an object by xml root element name
	 *
	 * @param DOMDocument $xml
	 * @return object
	 */
	protected function findObject(DOMDocument $xml) {
		$rootNodeName = $xml->documentElement->tagName;
		return $this->loadClass($rootNodeName);
	}
	
	/**
	 * Utility method to load an close
	 *
	 * @param string $className
	 * @return object
	 */
	protected function loadClass($className) {
		if (!class_exists($className)) {
			require_once $className.'.php';
		}
		return new $className();
	}
	
	/**
	 * This method sets the IDResolver.
	 * When you're planning to us XmlID's and the relations 
	 * you create with them and IDResolver in mendatory!
	 *
	 * @param IDResolverInterface $idResolver
	 */
	public function setIDResolver(IDResolverInterface $idResolver) {
		$this->_idResolver=$idResolver;
	}
	
	/**
	 * Return the property on $item that has the @XmlID annotation
	 *
	 * @param object $item
	 * @return the XmlID of the item, or null if none.
	 */
	private function findId($item){
		$REFPropertyAnno = new ReflectionAnnotate_PropertyAnnotation($item);
 		$propertyName = $REFPropertyAnno->getPropertyWithAnnotation("XmlID");
 		return $this->callGetter($item,$propertyName); 		
	}
	
	private function callGetter($object, $propertyName){
		$methodName = "get".ucfirst($propertyName);
		if(method_exists($object, $methodName)){
			$value = $object->$methodName();
		}else{
			$value = $object->$propertyName;
		}
		return $value;
	}
	
	private function createRefLinkElement(DOMDocument $xml, $item){	
		$className = get_class($item);
		$className[0] = strtolower($className[0]);
		$itemNode = $xml->createElement($className);
 		$itemNode->setAttribute("id", $this->findId($item));
 		return $itemNode;
	}
	
}

?>