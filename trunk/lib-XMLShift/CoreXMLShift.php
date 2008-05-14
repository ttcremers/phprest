<?
/**
 * @author ttcremers@gmail.com
 * @date 28-04-2008
 */
require_once 'ClassAnnotation.php';
require_once 'PropertyAnnotation.php';
require_once 'XMLShiftException.php';

class CoreXMLShift {
	private $_idResolver;
	
	/**
	 * Get xml rep for object
	 */
	function marshall($object) {
		try {
			$classAnno = new ReflectionAnnotate_ClassAnnotation($object);

			$xml = null;
			if ($classAnno->isAnnotationPressent("XmlRootElement")) {
				$rootNodeName = $classAnno->getAnnotationValue('XmlRootElement');
				$xml = new DOMDocument('1.0', 'UTF-8');
				$rootNode = $xml->appendChild($xml->createElement($rootNodeName));
				$propertyAnno = new ReflectionAnnotate_PropertyAnnotation($object);
				foreach (get_object_vars($object) as $key => $value) {
						
					// The XmlElement marker annotation superceeds all others
					if ($propertyAnno->isAnnotationPressent('XmlElement', $key)) {
						$element = $xml->createElement($key, $value);
						$rootNode->appendChild($element);
							
						// Add an attribute direct to the main node
					} else if ($propertyAnno->isAnnotationPressent('XmlAttribute', $key) &&
								!$propertyAnno->isAnnotationPressent('XmlContainerElement', $key)) {
						$rootNode->setAttribute($key, $value);
							
						// Create container with attribute. XmlContainerElement is not a Marker
						// Annotation.
					} else if ($propertyAnno->isAnnotationPressent('XmlAttribute', $key) &&
								$propertyAnno->isAnnotationPressent('XmlContainerElement', $key)) {
						$containerName = $propertyAnno->getAnnotationValue('XmlContainerElement', $key);
						$element = $xml->createElement($containerName);
						$element->setAttribute($key, $value);
						$rootNode->appendChild($element);
					} else if ($propertyAnno->isAnnotationPressent('XmlRef', $key) &&
								!$propertyAnno->isAnnotationPressent('XmlContainerElement', $key)) {
						// TODO marshall XMLRef 
					} else if ($propertyAnno->isAnnotationPressent('XmlRefList', $key)) {
						 if (is_array($value)) {
						 	// TODO loop over object array and create a new DOMNode
						 	$parentNode = $xml->createElement($key);
						 	$itemName = $propertyAnno->getAnnotationValue('XmlRefList', $key);
						 	foreach ($value as $item) {
						 		$itemNode = $xml->createElement($itemName);
						 		// Now find out which method is annotated with @XmlID
						 		$REFPropertyAnno = new ReflectionAnnotate_PropertyAnnotation($item);
						 		$propertyName = $REFPropertyAnno->getPropertyWithAnnotation("XmlID");
						 		// Set the value to the id attribute
						 		$itemNode->setAttribute("id", $item->$propertyName);
						 		$parentNode->appendChild($itemNode);
						 	}
						 	$rootNode->appendChild($parentNode);
						 } else {
						 	throw new XMLShiftException(
						 		"The XmlRefList annotation should annotate properties of type array"
						 	);
						 }
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
	 * @param object $object reference object that is used to unmarshall the xml. If it's not given an atemped will be made to autoload one.
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
			if ($propertyAnno->isAnnotationPressent('XmlElement', $objectProperty) &&
					!$propertyAnno->isAnnotationPressent('XmlContainerElement', $objectProperty)) {
				$node = $xml->getElementsByTagName($objectProperty)->item(0);
				$this->setObjectValue($node, $object, $objectProperty);
			
			} else {
				if ($propertyAnno->isAnnotationPressent('XmlContainerElement', $objectProperty)) {
					$containerElementName = $propertyAnno->getAnnotationValue('XmlContainerElement', $objectProperty);
					$container = $xml->getElementsByTagName($containerElementName)->item(0);
					if ($propertyAnno->isAnnotationPressent('XmlAttribute', $objectProperty)) {
						$attrNode = $container->getAttributeNode($objectProperty);
						$this->setObjectValue($attrNode, $object, $objectProperty);
					} else {
						$subElement = $container->getElementsByTagName($objectProperty)->item('0');
						$this->setObjectValue($subElement, $object, $objectProperty);	
					}
				} elseif ($propertyAnno->isAnnotationPressent('XmlRef', $objectProperty)) {
					$this->processXmlRef($xml->documentElement, $propertyAnno, $objectProperty, $object);
				} elseif ($propertyAnno->isAnnotationPressent('XmlRefList', $objectProperty)) {
					$this->lookupXmlRefList($xml->documentElement, $propertyAnno, $objectProperty, $object);
				}
			} 
		}
		return $object;
	}
	
	protected function lookupXmlRefList(DOMElement $node, 
						ReflectionAnnotate_PropertyAnnotation $propertyAnnotation,
						$objectProperty, $object) {
		$xmlRefClass = $propertyAnnotation->getAnnotationValue('XmlRefList', $objectProperty);
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
	
	protected function setObjectValue($node, $object, $property) {
		$value = $node->nodeValue;
		$method = "set".ucFirst($property);
		$object->$method($value);
	}

	protected function findObject(DOMDocument $xml) {
		$rootNodeName = $xml->documentElement->tagName;
		return $this->loadClass($rootNodeName);
	}
	
	protected function loadClass($className) {
		if (!class_exists($className)) {
			require_once $className.'.php';
		}
		return new $className();
	}
	
	public function setIDResolver(IDResolverInterface $idResolver) {
		$this->_idResolver=$idResolver;
	}
}

?>