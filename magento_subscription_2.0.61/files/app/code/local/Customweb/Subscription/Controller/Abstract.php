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
abstract class Customweb_Subscription_Controller_Abstract extends Mage_Core_Controller_Front_Action {

	/**
     * Try to load valid subscription by subscription_id and register it
     *
     * @param int $orderId
     * @return bool
     */
	protected function _loadValidSubscription($subscriptionId = null){
		if (null === $subscriptionId) {
			$subscriptionId = (int) $this->getRequest()->getParam('subscription_id');
		}
		if (!$subscriptionId) {
			$this->_forward('noRoute');
			return false;
		}

		$subscription = Mage::getModel('customweb_subscription/subscription')->load($subscriptionId);

		if ($this->_canViewSubscription($subscription)) {
			Mage::register('current_subscription', $subscription);
			return true;
		} else {
			$this->_redirect('*/index/');
		}
		return false;
	}

	/**
	 * Check subscription view availability
	 *
	 * @param   Customweb_Subscription_Model_Subscription $subscription
	 * @return  bool
	 */
	protected function _canViewSubscription($subscription)
	{
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		if ($subscription->getId() && $subscription->getCustomerId() && ($subscription->getCustomerId() == $customerId)) {
			return true;
		}
		return false;
	}

	/**
	 * Return the current order model.
	 *
	 * @return Mage_Sales_Model_Order
	 */
	protected function getOrder(){
		$id = Mage::getSingleton('core/session')->getSubscriptionOrder();

		if (!empty($id) && Mage::registry('subscription_order') == null) {
			Mage::register('subscription_order', Mage::getModel('sales/order')->load($id));
		}

		return Mage::registry('subscription_order');
	}
}