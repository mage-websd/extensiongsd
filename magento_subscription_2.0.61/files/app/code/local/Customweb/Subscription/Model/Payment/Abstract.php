<?php

/**
 *  * You are allowed to use this API in your web application.
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
 *
 * @category Customweb
 * @package Customweb_Subscription
 * @version 2.0.61
 */
class Customweb_Subscription_Model_Payment_Abstract extends Mage_Payment_Model_Method_Abstract {

	public function authorize(Varien_Object $payment, $amount){
		if ($this->getConfigData('invoice_settlement') == 'after_order') {
			$order = $payment->getOrder();
			$realOrderId = $payment->getOrder()->getRealOrderId();
			$order->loadByIncrementId($realOrderId);
			
			if ($order->canInvoice()) {
				$invoice = $order->prepareInvoice();
				$invoice->register();
				
				$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
				
				$transactionSave->save();
				
				if ($this->getConfigData('invoice_send_email')) {
					$invoice->sendEmail();
				}
			}
		}
		
		return $this;
	}
}