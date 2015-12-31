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
class Customweb_Subscription_Model_Sales_Order_Invoice_Total_InitAmount extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

	public function collect(Mage_Sales_Model_Order_Invoice $invoice){
		$initAmount = $invoice->getOrder()->getSubscriptionInitAmount();
		$baseInitAmount = $invoice->getOrder()->getBaseSubscriptionInitAmount();
		$invoice->setGrandTotal($invoice->getGrandTotal() + $initAmount);
		$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseInitAmount);

		return $this;
	}
}