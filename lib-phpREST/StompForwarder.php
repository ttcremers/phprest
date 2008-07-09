<?php
require_once 'Observer.php';
require_once 'Stomp/Stomp.php';

class StompForwarder implements Observer{

	private $brokerUrl;
	private $queue;

	public function StompForwarder(RESTServiceConfig $serviceConfig){
		$this->brokerUrl = $serviceConfig->observerSection["broker-url"];
		$this->queue = $serviceConfig->observerSection["queue"];
	}

	public function notify($action, $value){
		if($action == "CREATE" | $action == "UPDATE"){
			$stomp = new Stomp($this->brokerUrl);
			$stomp->connect();
			$stomp->send($this->queue, $value, null, true);
			$stomp->disconnect();
		}
	}
}
?>