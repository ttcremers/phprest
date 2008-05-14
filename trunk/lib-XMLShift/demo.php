<?php
/**
 * @XmlRootElement: demo
 */
class Demo {

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
	 * @XmlRefList:RefDemo
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
}
?>