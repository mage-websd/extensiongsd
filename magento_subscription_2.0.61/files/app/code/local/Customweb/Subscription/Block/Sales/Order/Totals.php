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
class Customweb_Subscription_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals {

	protected function _initTotals(){
		parent::_initTotals();
		$initAmount = $this->getOrder()->getSubscriptionInitAmount();
		if ($initAmount != 0) {
			$this->addTotal(
					new Varien_Object(
							array(
								'code' => 'subscription_init_amount',
								'value' => $initAmount,
								'base_value' => $initAmount,
								'label' => $initAmount > 0 ? Mage::helper('customweb_subscription')->__('Initial Subscription Fee') : Mage::helper(
										'customweb_subscription')->__('Initial Subscription Discount') 
							)), $initAmount > 0 ? 'fee' : 'discount');
		}
		return $this;
	}
}