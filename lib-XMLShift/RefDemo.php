<?php
/**
 * @XmlRootElement: demo
 */
class RefDemo {

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
}
?>