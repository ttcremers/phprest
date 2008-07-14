<?php
require_once 'Observer.php';
require_once 'Stomp/Stomp.php';

class StompForwarder implements Observer{

	private $brokerUrl;
	private $validResources;
	private $queue;

	/**
	 * @var CoreXMLShift
	 */
	private $shift;

	public function StompForwarder(RESTServiceConfig $serviceConfig){
		$this->brokerUrl = $serviceConfig->observerSection["broker-url"];
		$this->queue = $serviceConfig->observerSection["queue"];
		$this->shift = new CoreXMLShift();
		$this->shift->setIDResolver($this->loadIdResolver($serviceConfig));
	}

	public function notify($action, $value, $resource = null){

		if(is_object($value)){
			$message = $this->shift->marshall($value);
		}else{
			$message = $value;
		}

		error_log("FORWARD: {$message}");
		if($action == "CREATE" | $action == "UPDATE"){
			$stomp = new Stomp($this->brokerUrl);
			$stomp->connect();
			$stomp->send($this->queue, $message, array("resource" => $resource), false);
			$stomp->disconnect();
		}
	}

	private function loadIdResolver(RESTServiceConfig $serviceConfig){
		// Load the IDResolver if any
		$xmlIDResolverClass = $serviceConfig->adapterSection['xml-idresolver-class'];
		if ($xmlIDResolverClass) {
			$resolver = CoreUtil::loadCoreClass($xmlIDResolverClass);
		}
		return $resolver;
	}
}
?>