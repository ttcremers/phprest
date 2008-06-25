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
	 * @var IDResolver
	 */
	private $_idResolver;

	private $XLINK_URI = "http://www.w3.org/1999/xlink";

	private $_schemalocation;

	/**
	 * @param object $object XMLShift annotated object.
	 * @return String XML representation of the passed object.
	 */
	function marshall($object) {
		if(!is_object($object)) throw new UnexpectedValueException("Expected object, got ".gettype($object));

		$classAnno = new ReflectionAnnotate_ClassAnnotation($object);

		$xml = new DOMDocument('1.0', 'UTF-8');
		$xml->appendChild($this->convertObjectToXml($object, $xml));
		$xml->documentElement->setAttribute("xmlns:xlink",$this->XLINK_URI);

		if($classAnno->isAnnotationPresent("XmlNamespace")){
			$namespace = $classAnno->getAnnotationValue("XmlNamespace");
			$xml->documentElement->setAttribute("xmlns", $namespace);
		}

		return $xml->saveXML();
	}

	function convertObjectToXml($object, DOMDocument $xml){
		if(!is_object($object)) throw new UnexpectedValueException("Trying to convert a non-object to XML");

		$classAnno = new ReflectionAnnotate_ClassAnnotation($object);
		$propertyAnno = new ReflectionAnnotate_PropertyAnnotation($object);

		if($classAnno->isAnnotationPresent("XmlRootElement")){
			$rootNodeName = $classAnno->getAnnotationValue("XmlRootElement");
		}else{
			$rootNodeName = lcfirst(get_class($object));
		}

		$rootNode = $xml->createElement($rootNodeName);

		foreach (get_object_vars($object) as $key => $value) {

			// We need to call getter, variables may be there just for annotation
			$value = $this->callGetter($object,$key);
			if(!isset($value) && !$propertyAnno->isAnnotationPresent("XmlIncludeWhenEmpty",$key)) continue;

			if($propertyAnno->isAnnotationPresent("XmlID",$key)){
				$value = $this->_idResolver->reverse($object);
			}

			if($propertyAnno->isAnnotationPresent("XmlContainerElement", $key)){
				$parentNode = $xml->createElement($propertyAnno->getAnnotationValue("XmlContainerElement", $key));
				$rootNode->appendChild($parentNode);
			}else{
				$parentNode = $rootNode;
			}

			if($propertyAnno->isAnnotationPresent("XmlElement", $key)){
				if(isset($value) && !is_scalar($value)){
					throw new UnexpectedValueException("@XmlElement should be on a scalar value, but found ".gettype($value));
				}

				$xmlElementAnnValue = $propertyAnno->getAnnotationValue("XmlElement",$key);
				if($xmlElementAnnValue){
					$childElement = $xml->createElement($xmlElementAnnValue, $value);
				}else{
					$childElement = $xml->createElement($key,$value);
				}
				$parentNode->appendChild($childElement);

			}else if($propertyAnno->isAnnotationPresent("XmlAttribute", $key)){

				if(isset($value) && !is_scalar($value)){
					throw new UnexpectedValueException("@XmlAttribute should be on a scalar value, but found ".gettype($value));
				}

				$xmlAttributeAnnValue = $propertyAnno->getAnnotationValue("XmlAttribute",$key);
				if($xmlAttributeAnnValue){
					$parentNode->setAttribute($xmlAttributeAnnValue, $value);
				}else{
					$parentNode->setAttribute($key, $value);
				}

			}else if($propertyAnno->isAnnotationPresent("XmlRefLink", $key)){
				if(isset($value) && !is_object($value)){
					throw new UnexpectedValueException("@XmlRefLink should be on an object value, but found ".gettype($value));
				}
				$parentNode->appendChild($this->createRefLinkElement($xml, $value));
			}else if( $propertyAnno->isAnnotationPresent("XmlRefLinkMany", $key) ){
				if(isset($value) && !is_array($value)){
					throw new UnexpectedValueException("@XmlRefLinkMany should be on an array value, but found ".gettype($value));
				}
				foreach ($value as $item) {
					$parentNode->appendChild($this->createRefLinkElement($xml, $item));
				}

			}else if($propertyAnno->isAnnotationPresent("XmlRef", $key)){
				// Due to the way Propel handles relations, this will probable not apply for Propel based apps.
				if(!is_object($value)){
					throw new UnexpectedValueException("@XmlRef should be on object value, but found ".gettype($value)." (try @XmlRefMany)");
				}
				$parentNode->appendChild($this->convertObjectToXml($value, $xml));
			}else if($propertyAnno->isAnnotationPresent("XmlRefMany", $key)){
				if(isset($value) && !is_array($value)){
					throw new UnexpectedValueException("@XmlRef should be on array value, but found ".gettype($value));
				}

				foreach($value as $member){
					$parentNode->appendChild($this->convertObjectToXml($member,$xml));
				}
			}else if($propertyAnno->isAnnotationPresent("XmlTextnode", $key)){
				if(!is_scalar($value)){
					throw new UnexpectedValueException("@XmlTextnode should be on a scalar value, but found ".gettype($value));
				}
				$parentNode->nodeValue = $value;
			}
		}
		return $rootNode;
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

		$xpath = new DOMXPath($xml);
		$xpath->registerNamespace("koms",$xml->documentElement->getAttribute("xmlns"));

		if($this->_schemalocation)
			$this->validate($xml,$this->_schemalocation);

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

			if($propertyAnno->isAnnotationPresent('XmlContainerElement', $objectProperty)){
				$containerElementName = $propertyAnno->getAnnotationValue('XmlContainerElement', $objectProperty);
				$node = $xml->getElementsByTagName($containerElementName)->item(0);
			} else{
				$node = $xml->documentElement;
			}

			if ($propertyAnno->isAnnotationPresent('XmlElement', $objectProperty)) {
				$expr = $this->buildXPathExpression($object, $objectProperty);
				$node = $xpath->query($expr, $xml->documentElement)->item(0);

				$this->setObjectValue($node, $object, $objectProperty);
			} elseif ($propertyAnno->isAnnotationPresent('XmlAttribute', $objectProperty)) {
				$attrName = $propertyAnno->getAnnotationValue('XmlAttribute', $objectProperty);
				if(!$attrName) $attrName = $objectProperty;

				$attrNode = $node->getAttributeNode($attrName);
				$this->setObjectValue($attrNode, $object, $objectProperty);
			} elseif ($propertyAnno->isAnnotationPresent('XmlRefMany', $objectProperty)) {
				$this->processXmlRefMany($xml->documentElement, $propertyAnno, $objectProperty, $object);
			} elseif ($propertyAnno->isAnnotationPresent('XmlRefLinkMany', $objectProperty)) {
				$this->lookupXmlRefList($xml->documentElement, $propertyAnno, $objectProperty, $object);
			} elseif ($propertyAnno->isAnnotationPresent('XmlRefLink', $objectProperty)) {
				$this->lookupXmlRefId($xml->documentElement, $propertyAnno, $objectProperty, $object);
			} elseif ($propertyAnno->isAnnotationPresent('XmlTextnode', $objectProperty)) {
				$this->setObjectValue($xml->documentElement, $object, $objectProperty);
			}
		}
		return $object;
	}

	/**
	 * Looks up an object based on the information in the xml.
	 * The IDResolver is used to lookup the object with the id's found in the xml.
	 * @see IDResolver
	 *
	 * @param DOMElement $node
	 * @param ReflectionAnnotate_PropertyAnnotation $propertyAnnotation
	 * @param string $objectProperty Name of the property where we set the object
	 * @param object $object to object on which we call the setter with the object
	 */
	protected function lookupXmlRefId(DOMElement $node,
						ReflectionAnnotate_PropertyAnnotation $propertyAnnotation,
						$objectProperty, $object) {
							//TODO rename to lookupXmlRefLink

		$xmlRefClass = $propertyAnnotation->getAnnotationValue('XmlRefLink', $objectProperty);

		$node = $node->getElementsByTagName($objectProperty)->item(0);
		$node = $node->getElementsByTagName('*')->item(0);

		$xmlHref = $node->getAttributeNS($this->XLINK_URI, "href");
		if($xmlHref){
			$resolvedObject = $this->_idResolver->resolveURL($xmlHref);
		}

		$method = "set".ucFirst($objectProperty);
		$object->$method($resolvedObject);

	}

	/**
	 * Looks up and creates a list of objects based on the information in the xml.
	 * The IDResolver is used to lookup the objects with the id's found in the xml.
	 * @see IDResolver
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
		for ($i=0; $i<= ($refChilderen->length)-1; $i++) {
			if ($refChilderen->item($i)->tagName==lcfirst($xmlRefClass)) { //TODO get the XML root element value from the xmlRefClass
				$hrefAttr = $refChilderen->item($i)->getAttributeNS($this->XLINK_URI,"href");
				if($hrefAttr){
					array_push($objectList, $this->_idResolver->resolveURL($hrefAttr));
				}
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
	protected function processXmlRefMany(DOMElement $node,
						ReflectionAnnotate_PropertyAnnotation $propertyAnnotation,
						$objectProperty, $object) {
		$xmlRefClass = $propertyAnnotation->getAnnotationValue('XmlRefMany', $objectProperty);

		$refNodeList = $node->getElementsByTagName(lcfirst($xmlRefClass));
		$arr = array();

		for ($i=0; $i<= ($refNodeList->length)-1; $i++) {
			$refNode = $refNodeList->item($i);

			$xmlRefObject = $this->loadClass($xmlRefClass);

			// Create a new DOMDocument with which we can marshall
			$newDom = new DOMDocument('1.0', 'UTF-8');
			$refNode = $newDom->importNode($refNode, true);
			$newDom->appendChild($refNode);

			// Now unmarshall the node to object.
			$xmlRefObject = $this->unMarshall($newDom, $xmlRefObject);
			$arr[] = $xmlRefObject;
		}

		$method = "set".ucFirst($objectProperty);
		$object->$method($arr);
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
		//TODO this forces rootNodeName == classname. Makes this more flexible.
		$rootNodeName = ucfirst($xml->documentElement->tagName);
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
	 * @param IDResolver $idResolver
	 */
	public function setIDResolver(IDResolver $idResolver) {
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
		$className = lcfirst(get_class($item));
		$itemNode = $xml->createElement($className);
 		$itemNode->setAttribute("xlink:href", $this->_idResolver->constructURL($item));
 		return $itemNode;
	}

	public function validate(DOMDocument $xml, $schemaFile){
		libxml_use_internal_errors(true);
		libxml_clear_errors();
		$xml->relaxNGValidate($schemaFile);
		$errors = libxml_get_errors();
		$errorMsg;
		if (count($errors)){
			foreach ($errors as $error) {
				$message = trim($error->message);
				$errorMsg .= "\r\n* {$message}";
			}
			throw new XMLShiftException("Validation against schema failed:{$errorMsg}", 400);
		}
	}

	public function setSchemaLocation($filename){
		$this->_schemalocation = $filename;
	}

	/**
	 * Builds an XPathExpression to get to the given property withing the given object.
	 */
	public function buildXPathExpression($object, $property){
		$expr = '';
		$classAnno = new ReflectionAnnotate_ClassAnnotation($object);
		$propertyAnno = new ReflectionAnnotate_PropertyAnnotation($object);

		$rootElement = $classAnno->getAnnotationValue("XmlRootElement",$property);
		if(!$rootElement)  $rootElement = lcfirst(get_class($object));
		$expr .= "//koms:".$rootElement;

		if($propertyAnno->isAnnotationPresent("XmlTextnode", $property)){
			return $expr;
		}

		if($propertyAnno->isAnnotationPresent("XmlContainerElement", $property)){
			$containerName = $propertyAnno->getAnnotationValue("XmlContainerElement", $property);
			$expr .= "/{$containerName}";
		}

		if($propertyAnno->isAnnotationPresent("XmlElement", $property)){
			$elementName = $propertyAnno->getAnnotationValue("XmlElement", $property);
			if(!$elementName) $elementName = $property;

			$expr .= "/koms:{$elementName}";
		}elseif($propertyAnno->isAnnotationPresent("XmlAttribute", $property)){
			$attributeName = $propertyAnno->getAnnotationValue("XmlAttribute", $property);
			if(!$attributeName) $attributeName = $property;

			$expr .= "/@{$attributeName}";
		}else{
			// TODO implement for XmlRef, XmlRefMany, XmlRefLink, XmlRefLinkMany
			throw new XMLShiftException("NOT IMPLEMENTED");
		}

		return $expr;
	}
}

function lcfirst($string){
	$string[0] = strtolower($string[0]);
	return $string;
}

?>