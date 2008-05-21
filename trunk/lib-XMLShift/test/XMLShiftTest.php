#!/usr/bin/php
<?php
/** 
 * @author ttcremers@gmail.com
 * @copyright Lunatech Research B.V. 2008
 * @package XMLShift
 */

$sys_include_path=ini_get('include_path');
$lib_reflection_path=realpath("../../lib-ReflectionAnnotate");
ini_set('include_path', "$sys_include_path:../:$lib_reflection_path:/usr/share/php");

require_once 'PHPUnit/Framework.php';
require_once 'CoreXMLShift.php';
require_once 'IDResolverInterface.php';

/**
 * Unit tests
 * 
 * This class is based on pear.phpunit.de
 * you have to manualy add this channel as
 * the phpunit is the standard channel is  
 * outdated and unmaintained!
 * 
 * To test: phpunit XMLShiftTest.php
 */
class XMLShiftTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var CoreXMLShift
	 */
	protected $xmlShift;
	protected $xmlDemoObject;
	protected $xmlDemoRefObject;	
	protected $xmlString;
	
	// Fixture Methods
	function setUp() {
		$this->xmlString='<?xml version="1.0" encoding="UTF-8"?>
<demo><user name="Test Kees"/><age>99</age><refs><RefDemo id="1"/></refs></demo>';
		
		$this->xmlDemoRefObject = new xmlDemoRefObject();
		$this->xmlDemoRefObject->setID(1);
		
		$this->xmlDemoObject = new xmlDemoObject();
		$this->xmlDemoObject->setName("Test Kees");
		$this->xmlDemoObject->setAge(99);
		$this->xmlDemoObject->setRefs(array($this->xmlDemoRefObject));
		
		$this->xmlShift = new CoreXMLShift();
		$this->xmlShift->setIDResolver(new MockIdResolver());
	}
	
	function teardown() {
		$this->xmlDemoObject = NULL;
		$this->xmlDemoRefObject = NULL;
		$this->xmlShift = NULL;
		$this->xmlString = NULL;
	}
	
	// The Tests
	public function test_basic_marshall() {
		$this->xmlDemoRefObject->setMessage("Simpel xml object");
		$xml = $this->xmlShift->marshall($this->xmlDemoRefObject);
		$domdoc = new DOMDocument('1.0','UTF-8');
		$domdoc->loadXML($xml);
		$messageELM = $domdoc->getElementsByTagName('message')->item(0);
		$this->assertEquals("Simpel xml object", $messageELM->nodeValue);
	}
	
	public function test_basic_unmarshall() {
		$object = $this->xmlShift->unmarshall('<?xml version="1.0" encoding="UTF-8"?>
<demo><message>Simpel xml object</message></demo>', new XmlDemoRefObject());
		$this->assertEquals("Simpel xml object", $object->message);
	}
	
	public function test_hasa_unmarshall() {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<demohasa><refobject id="1"/></demohasa>';
		$object = $this->xmlShift->unmarshall($xml, new XmlDemoHasaObject());
		if (!is_object($object->refObject))
			$this->fail("was expecting an object but got: ".$object->refObject); 
	}
	
	public function test_hasa_marshall() {
		$parent = new XmlDemoHasaObject();
		$child = new XmlDemoRefObject();
		$child->setID("NCC-74656");
		$parent->setRefObject($child);
		
		$xml = $this->xmlShift->marshall($parent);
		
		$targetXML = '<?xml version="1.0" encoding="UTF-8"?>
		<demohasa><refObject><XmlDemoRefObject id="NCC-74656"/></refObject></demohasa>';

		$this->assertXmlEqual($xml, $targetXML);
		
	}
	
	// Note that this is by no means perfect. Ordering matters, it shouldn't.
	private function assertXmlEqual($xml, $expected){
		$document = new DOMDocument();
		$document->loadXML($xml);
		$document->normalizeDocument();
		
		$expectedDocument = new DOMDocument();
		$expectedDocument->loadXML($expected);
		$expectedDocument->normalizeDocument();
		
		$expectedXML = $expectedDocument->saveXML();
		$actualXML = $document->saveXML();
		if( $expectedXML != $actualXML){
			$this->fail("XML fragments do not match\r\nExpected:$expectedXML\r\nGot:\r\n$actualXML");
		}
	}
	
	public function test_relational_marshall() {
		$this->assertTrue(true);
	}
	
	public function test_relational_unmarshall() {
		$this->assertTrue(true);
	}
};


// Some simple test objects with which we can (un)marshall

/**
 * @package XMLShift
 * 
 * @XmlRootElement: demo
 */
class XmlDemoObject {
	/**
	 * @XmlContainerElement: user
	 * @XmlAttribute
	 */ 
	public $name;
	
	/**
	 * @XmlElement
	 */
	public $age;
	
	/**
	 * @XmlRefLinkMany:XmlDemoRefObject
	 */
	public $refs = array();
	
	public function setName($name) {
		$this->name=$name;
	}
	
	public function setAge($age) {
		$this->age=$age;
	}
	
	public function setRefs(array $refs) {
		$this->refs=$refs;
	}
};

/**
 * @package XMLShift
 * 
 * @XmlRootElement: demo
 */
class XmlDemoRefObject {
	/**
	 * @XmlID
	 */ 
	public $id;
	
	/**
	 * @XmlElement
	 */
	public $message="I'm a reference object!";
		
	public function setID($id) {
		$this->id=$id;
	}
	
	public function setMessage($message) {
		$this->message=$message;
	}
};

/**
 * @package XMLShift
 * 
 * @XmlRootElement: demohasa
 */
class XmlDemoHasaObject {
	/**
 	 * @XmlRefLink:XmlDemoRefObject
 	 */
	public $refObject;
	
	public function setRefObject($object) {
		$this->refObject=$object;
	}
}

class MockIdResolver implements IDResolverInterface{
	public function resolve($id,$className){
		$instance = new $className();
		$instance->setId($id);
		return $instance;
	}
}
?>