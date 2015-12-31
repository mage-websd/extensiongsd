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
 * Override the quote model to add custom behaviour.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Sales_Quote extends Mage_Sales_Model_Quote {

	/**
	 * @var boolean
	 */
	private $isItemUpdate = false;

	/**
	 * Prepare data before save
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _beforeSave(){
		if (Mage::helper('customweb_subscription/cart')->canUseCartAsSubscription($this)) {
			$subscriptionPlan = $this->getSubscriptionPlan();
			if (!is_string($subscriptionPlan) && $subscriptionPlan != null) {
				$this->setSubscriptionPlan(base64_encode(serialize($subscriptionPlan)));
			}
		}
		else {
			$this->setSubscriptionPlan(null);
		}

		parent::_beforeSave();
	}

	/**
	 * Save related items
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _afterSave(){
		parent::_afterSave();

		$subscriptionPlan = $this->getSubscriptionPlan();
		if (is_string($subscriptionPlan)) {
			$subscriptionPlan = empty($subscriptionPlan) ? null : unserialize(base64_decode($subscriptionPlan));
			$this->setSubscriptionPlan($subscriptionPlan);
		}
	}

	/**
	 * Trigger collect totals after loading, if required
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _afterLoad(){
		$subscriptionPlan = $this->getSubscriptionPlan();
		if (is_string($subscriptionPlan)) {
			$subscriptionPlan = empty($subscriptionPlan) ? null : unserialize(base64_decode($subscriptionPlan));
			$this->setSubscriptionPlan($subscriptionPlan);
		}

		return parent::_afterLoad();
	}

	/**
	 * Make sure a recurring product can only be bought by itself.
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return Customweb_Subscription_Model_Quote
	 */
	public function addItem(Mage_Sales_Model_Quote_Item $item){
		if (!$this->isItemUpdate && $item->getProduct()->getParentProductId() == null && (($item->isSubscription() && $this->hasItems()) || $this->hasSubscriptionItems())) {
			Mage::throwException(
					Mage::helper('customweb_subscription')->__('Subscriptions can be purchased standalone only. To proceed please remove other items from the quote.'));
		}

		return parent::addItem($item);
	}

	/**
	 * Make sure recurring items can be updated.
	 *
	 * @param int $itemId
	 * @param Varien_Object $buyRequest
	 * @param null|array|Varien_Object $params
	 * @return Mage_Sales_Model_Quote_Item
	 */
	public function updateItem($itemId, $buyRequest, $params = null)
	{
		$this->isItemUpdate = true;
		$result = parent::updateItem($itemId, $buyRequest, $params);
		$this->isItemUpdate = false;
		return $result;
	}

	/**
	 * Check whether there are recurring items in the cart.
	 *
	 * @return boolean
	 */
	protected function hasSubscriptionItems(){
		foreach ($this->getAllItems() as $item) {
			if ($item->isSubscription()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check whether this quote contains a subscription product.
	 *
	 * @return boolean
	 */
	public function isSubscription(){
		return $this->getSubscriptionPlan() != null || $this->hasSubscriptionItems();
	}

	/**
	 * Return the item containing the subscription product.
	 *
	 * @return Mage_Sales_Model_Quote_Item
	 */
	public function getSubscriptionItem(){
		foreach ($this->getAllItems() as $item) {
			if ($item->isSubscription() && $item->getParentItemId() == null) {
				return $item;
			}
		}
	}

	public function getRecurringCosts(){
		$helper = Mage::helper('customweb_subscription');
		$totalsToDisplay = array();
		if (Mage::getStoreConfig('customweb_subscription/general/calculate_price')) {
			Mage::register('customweb_subscription_recurring_order', true);
			$totals = $this->getTotals();
			Mage::unregister('customweb_subscription_recurring_order');
			$this->setTotalsCollectedFlag(false);
			$this->collectTotals();
			foreach ($totals as $total) {
				if ($total->getCode() == 'subscription_init_amount')
					continue;
				$totalsToDisplay[] = array(
					'code' => $total->getCode(),
					'title' => ($total->getCode() == 'grand_total') ? $helper->__('Total Recurring Amount') : $total->getTitle(),
					'value' => $total->getValue()
				);
			}
		}
		else {
			$billingAmount = 0;
			$taxAmount = 0;
			foreach ($this->getAllItems() as $item) {
				if ($item->getParentItemId() == null) {
					$billingAmount += $item->getRowTotal();
					$taxAmount += $item->getTaxAmount();
				}
			}
			if ($subscriptionItem = $this->getSubscriptionItem()) {
				$subscriptionInfos = $subscriptionItem->getProduct()->getSubscriptionInfos();
			}
			else {
				$subscriptionInfos = array(
					'shipping_amount_type' => 'calculated'
				);
			}
			if ($subscriptionInfos['shipping_amount_type'] == 'fixed') {
				$shippingAmount = $subscriptionInfos['shipping_amount'];
			}
			else {
				$shippingAmount = $this->getShippingAddress()->getShippingAmount();
			}
			$totalsToDisplay = array(
				array(
					'code' => 'subtotal',
					'title' => $helper->__('Subtotal'),
					'value' => $billingAmount
				),
				array(
					'code' => 'tax',
					'title' => $helper->__('Tax'),
					'value' => $taxAmount
				),
				array(
					'code' => 'shipping',
					'title' => $helper->__('Shipping'),
					'value' => $shippingAmount
				),
				array(
					'code' => 'total',
					'title' => $helper->__('Total Recurring Amount'),
					'value' => $billingAmount + $shippingAmount + $taxAmount
				)
			);
		}
		return $totalsToDisplay;
	}
}