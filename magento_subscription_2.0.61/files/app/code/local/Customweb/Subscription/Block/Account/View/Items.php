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
class Customweb_Subscription_Block_Account_View_Items extends Mage_Sales_Block_Items_Abstract {

	/**
	 * Retrieve required options from parent
	 */
	protected function _beforeToHtml(){
		if (!$this->getParentBlock()) {
			Mage::throwException(Mage::helper('adminhtml')->__('Invalid parent block for this block.'));
		}
		$this->setSubscription($this->getParentBlock()->getSubscription());
		
		$orderInfos = $this->getSubscription()->getOrderInfo();
		$order = Mage::getModel('sales/order')->setData($orderInfos)->setId(null);
		$this->setOrder($order);
		
		parent::_beforeToHtml();
	}
}