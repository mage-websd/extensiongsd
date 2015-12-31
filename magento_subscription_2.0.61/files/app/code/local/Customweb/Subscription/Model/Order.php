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

/**
 * Represents a relation between an order and a subscription.
 *
 * @author Simon Schurter
 *
 * @method int getId()
 * @method int getSubscriptionId()
 * @method Customweb_Subscription_Model_Order setSubscriptionId(int $value)
 * @method int getOrderId()
 * @method Customweb_Subscription_Model_Order setOrderId(int $value)
 */
class Customweb_Subscription_Model_Order extends Mage_Core_Model_Abstract {
	
	/**
	 * Event prefix and object
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'customweb_subscription_order';
	protected $_eventObject = 'order';
	
	/**
	 *
	 * @var Mage_Sales_Model_Order
	 */
	private $_order = null;
	
	/**
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	private $_subscription = null;

	protected function _construct(){
		$this->_init('customweb_subscription/order');
	}

	/**
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder(){
		if ($this->_order == null) {
			$this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
		}
		return $this->_order;
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		if ($this->_subscription == null) {
			$this->_subscription = Mage::getModel('customweb_subscription/subscription')->load($this->getSubscriptionId());
		}
		return $this->_subscription;
	}
}