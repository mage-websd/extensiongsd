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
class Customweb_Subscription_Block_Checkout_Agreements_Subscription extends Mage_Core_Block_Template {
	
	/**
	 * Path to template file in theme.
	 *
	 * @var string
	 */
	protected $_template = 'customweb/subscription/checkout/agreements/subscription.phtml';
	
	/**
	 * Product containing the subscription information.
	 *
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_product = null;

	protected function getProduct(){
		if ($this->_product == null) {
			$this->_product = Mage::getModel('checkout/cart')->getQuote()->getSubscriptionItem()->getProduct();
		}
		return $this->_product;
	}

	public function getRecurringCosts(){
		$quote = Mage::getModel('checkout/cart')->getQuote();
		return $quote->getRecurringCosts();
	}

	public function getDescription(){
		$quote = Mage::getModel('checkout/cart')->getQuote();
		if ($quote->getSubscriptionPlan() != null) {
			return $quote->getSubscriptionPlan()->getDescription();
		}
		else {
			return $this->getProduct()->getScheduleDescription();
		}
	}

	public function getPlan(){
		$quote = Mage::getModel('checkout/cart')->getQuote();
		if ($quote->getSubscriptionPlan() != null) {
			return $quote->getSubscriptionPlan()->getPlan();
		}
		else {
			return $this->getProduct()->getPlan();
		}
	}

	public function getCancelPeriod(){
		$quote = Mage::getModel('checkout/cart')->getQuote();
		if ($quote->getSubscriptionPlan() != null) {
			return $quote->getSubscriptionPlan()->getCancelPeriod();
		}
		else {
			return $this->getProduct()->getCancelPeriod();
		}
	}

	public function getTitle(){
		return Mage::getStoreConfig('customweb_subscription/checkout/agreement_title');
	}

	public function getText(){
		return Mage::getStoreConfig('customweb_subscription/checkout/agreement_text');
	}
}