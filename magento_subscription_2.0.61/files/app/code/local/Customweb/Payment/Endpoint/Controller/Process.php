<?php 
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2015 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Payment/Endpoint/Controller/Abstract.php';


/**
 * Implementation of a controller for processing payment notifications.
 * 
 * @author Thomas Hunziker
 *
 */
abstract class Customweb_Payment_Endpoint_Controller_Process extends Customweb_Payment_Endpoint_Controller_Abstract {
	
	/**
	 * 
	 * @Action("index")
	 */
	public function process(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request) {
		
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$parameters = $request->getParameters();
		$response = $adapter->processAuthorization($transaction, $parameters);
		return $response;
	}
}