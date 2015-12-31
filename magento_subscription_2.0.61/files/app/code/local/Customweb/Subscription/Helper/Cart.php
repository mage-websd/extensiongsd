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
class Customweb_Subscription_Helper_Cart extends Mage_Core_Helper_Abstract {
	
	/**
	 * Is quote recurring
	 *
	 * @var boolean
	 */
	protected $_isQuoteRecurring = null;

	/**
	 * Returns whether cart subscriptions are allowed.
	 *
	 * @return boolean
	 */
	public function isCartSubscriptionEnabled(){
		return (boolean) Mage::getStoreConfig('customweb_subscription/cart/enabled');
	}

	/**
	 * Check whether the cart can be used as subscription.
	 *
	 * @return boolean
	 */
	public function canUseCartAsSubscription($quote = null){
		if ($quote === null) {
			$quote = Mage::getModel('checkout/cart')->getQuote();
		}
		
		$minTotal = Mage::getStoreConfig('customweb_subscription/cart/min_order_total');
		$maxTotal = Mage::getStoreConfig('customweb_subscription/cart/max_order_total');
		
		$total = $quote->getBaseGrandTotal();
		if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
			return false;
		}
		
		foreach ($quote->getAllItems() as $item) {
			if (!$this->canCartSubscribeProduct($item->getProduct())) {
				return false;
			}
		}
		
		$plans = $this->getCartSubscriptionPlans();
		if (empty($plans)) {
			return false;
		}
		
		return true;
	}

	/**
	 * Return whether the product can be used in cart subscriptions.
	 *
	 * @param int|Mage_Catalog_Model_Product $product
	 * @return boolean
	 */
	public function canCartSubscribeProduct($product){
		$productFilter = Mage::getStoreConfig('customweb_subscription/cart/product_filter');
		if (!is_array($productFilter)) {
			$productFilter = explode(',', $productFilter);
		}
		
		if (!($product instanceof Mage_Catalog_Model_Product)) {
			$product = Mage::getModel('catalog/product')->load($product);
		}
		
		if (!in_array($product->getId(), $productFilter) && !$product->isSubscription()) {
			return true;
		}
		return false;
	}

	/**
	 * Get all available cart subscription plans.
	 *
	 * @return Customweb_Subscription_Model_CartPlan[]
	 */
	public function getCartSubscriptionPlans(){
		$plans = Mage::getStoreConfig('customweb_subscription/cart/plans');
		if (!is_array($plans)) {
			$plans = empty($plans) ? false : unserialize(base64_decode($plans));
		}
		return $plans;
	}

	/**
	 * Check whether the current quote contains a subscription product.
	 *
	 * @return boolean
	 */
	public function isQuoteRecurring(){
		if ($this->_isQuoteRecurring == null) {
			$this->_isQuoteRecurring = Mage::getModel('checkout/cart')->getQuote()->isSubscription();
		}
		return $this->_isQuoteRecurring;
	}

	/**
	 * Check whether the order contains a recurring product.
	 *
	 * @return boolean
	 */
	public function isOrderSubscription(Mage_Sales_Model_Order $order){
		$link = Mage::getModel('customweb_subscription/order')->load($order->getId(), 'order_id');
		if ($link->getId()) {
			return true;
		}
	}
}