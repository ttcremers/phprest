<?php

require_once 'CoreService.php';

class TransactionalService extends CoreService  {

	public function service(Request $request, Response $response, AdapterInterface $contentAdapter, RESTServiceConfig $serviceConfig) {
		$con = Propel::getConnection();
		$con->beginTransaction();
		try{
			parent::service($request, $response, $contentAdapter, $serviceConfig);
			$con->commit();
            parent::notifyObservers();
		}catch (Exception $e){
			error_log("Exception caught, rolling back. {$e->getMessage()}");
			$con->rollBack();
			throw $e;
		}
	}

}
?>