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
 * Contains creation related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Create extends Customweb_Subscription_Model_Subscription_Abstract {

	/**
	 * Submit a subscription right after an order is placed
	 *
	 * @throws Exception
	 */
	public function submit(){
		$this->_getResource()->beginTransaction();
		try {
			$this->getSubscription()->setReferenceId(Mage::helper('core')->uniqHash('temporary-'));
			$this->save();
			$this->getSubscription()->setReferenceId($this->_getNewReferenceId($this->getSubscription()->getId()));
			$this->getSubscription()->setCalculatePrice(Mage::getStoreConfig('customweb_subscription/general/calculate_price'));
			$this->save();
			$this->_getResource()->commit();
		}
		catch (Exception $e) {
			$this->_getResource()->rollBack();
			throw $e;
		}
	}

	/**
	 * Import product subscription information.
	 * Throws exception if it cannot be imported.
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @throws Exeception
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function importProduct(Mage_Catalog_Model_Product $product){
		$subscriptionInfos = $product->getSubscriptionInfos();
		if (!is_array($subscriptionInfos)) {
			$subscriptionInfos = unserialize($subscriptionInfos);
		}
		if ($product->isSubscription() && is_array($subscriptionInfos)) {
			$this->getSubscription()->addData($subscriptionInfos);
			
			return $this->_filterValues();
		}
		throw new Exception('The product cannot be imported into the subscription.');
	}

	/**
	 * Import cart subscription plan information.
	 * Returns false if it cannot be imported.
	 *
	 * @param Customweb_Subscription_Model_CartPlan $cartPlan
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function importCartPlan(Customweb_Subscription_Model_CartPlan $cartPlan){
		$this->getSubscription()->addData($cartPlan->getInformation());
		return $this->_filterValues();
	}

	/**
	 * Import order information to the subscription.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function importOrder(Mage_Sales_Model_Order $order){
		$this->getSubscription()->setInitialOrderId($order->getId());
		
		if ($order->getPayment() && $order->getPayment()->getMethod()) {
			$this->getSubscription()->setMethodInstance($order->getPayment()->getMethodInstance());
			
			if (!$this->getSubscription()->isMethodSuspendOnPendingPayment()) {
				$this->getSubscription()->activate();
			}
		}
		
		$orderInfo = $order->getData();
		$this->_cleanupArray($orderInfo);
		$this->getSubscription()->setOrderInfo($orderInfo);
		
		$addressInfo = $order->getBillingAddress()->getData();
		$this->_cleanupArray($addressInfo);
		$this->getSubscription()->setBillingAddressInfo($addressInfo);
		if (!$order->getIsVirtual()) {
			$addressInfo = $order->getShippingAddress()->getData();
			$this->_cleanupArray($addressInfo);
			$this->getSubscription()->setShippingAddressInfo($addressInfo);
		}
		
		$this->getSubscription()->setCurrencyCode($order->getBaseCurrencyCode());
		
		$this->getSubscription()->setCustomerId($order->getCustomerId());
		
		if ($order->getCustomerId() != null) {
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
			$this->getSubscription()->setSubscriberName($customer->getName());
		}
		else {
			$this->getSubscription()->setSubscriberName($order->getBillingAddress()->getName());
		}
		
		$this->getSubscription()->setStoreId($order->getStoreId());
		
		$this->getSubscription()->setLastDatetime(Mage::helper('customweb_subscription')->toDateString(Zend_Date::now()));
		
		if ($order->getPayment()) {
			$this->getSubscription()->setPaymentId($order->getPayment()->getId());
		}
		
		if ($this->getSubscription()->getData('shipping_amount_type') == 'fixed') {
			$this->getSubscription()->setShippingAmount($this->getSubscription()->getData('shipping_amount'));
		}
		else {
			$this->getSubscription()->setShippingAmount($order->getShippingAmount() + $order->getShippingTaxAmount());
		}
		
		$this->getSubscription()->setInitAmount($order->getSubscriptionInitAmount());
		
		$this->getSubscription()->setLastOrderId($order->getId());
		
		return $this->getSubscription();
	}

	/**
	 * Import order item information to the subscription.
	 *
	 * @param Mage_Sales_Model_Order_Item $item
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function importOrderItems(array $items){
		$orderItemInfos = array();
		
		$billingAmount = 0;
		$taxAmount = 0;
		foreach ($items as $item) {
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			if ($item->getParentItemId() == null) {
				$billingAmount += $item->getRowTotal();
				$taxAmount += $item->getTaxAmount();
			}
			
			$orderItemInfo = $item->getData();
			$this->_cleanupArray($orderItemInfo);
			
			$customOptions = $item->getOptionsByCode();
			if ($customOptions['info_buyRequest']) {
				$orderItemInfo['info_buyRequest'] = $customOptions['info_buyRequest']->getValue();
			}
			
			$orderItemInfos[] = $orderItemInfo;
		}
		
		$this->getSubscription()->setBillingAmount($billingAmount);
		$this->getSubscription()->setTaxAmount($taxAmount);
		
		$this->getSubscription()->setOrderItemInfo($orderItemInfos);
		
		return $this->_filterValues();
	}

	/**
	 * Determine nearest possible subscription start date.
	 *
	 * @param Zend_Date $minAllowed
	 * @return Customweb_Subscription_Model_Subscription
	 */
	private function setNearestStartDatetime(Zend_Date $minAllowed = null){
		$startDate = $minAllowed;
		if (!$startDate || $startDate->getTimestamp() < time()) {
			$startDate = Zend_Date::now();
		}
		$this->getSubscription()->setStartDatetime(Mage::helper('customweb_subscription')->toDateString($startDate));
		return $this->getSubscription();
	}

	/**
	 * Filter self data to make sure it can be validated properly.
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	private function _filterValues(){
		// set status, if empty
		if ($this->getStatus() == null) {
			$this->setStatus(self::STATUS_UNKNOWN);
		}
		
		// determine payment method/code
		$this->getSubscription()->getMethodInstance();
		
		// unset redundant values, if empty
		foreach (array(
			'period_frequency',
			'period_max_cycles',
			'reference_id',
			'cancel_period' 
		) as $key) {
			if ($this->getSubscription()->hasData($key) && (!$this->getSubscription()->getData($key) || '0' == $this->getSubscription()->getData($key))) {
				$this->getSubscription()->unsetData($key);
			}
		}
		
		// cast amounts
		foreach (array(
			'billing_amount',
			'shipping_amount',
			'tax_amount',
			'init_amount' 
		) as $key) {
			if ($this->getSubscription()->hasData($key)) {
				if (!$this->getSubscription()->getData($key) || 0 == $this->getSubscription()->getData($key)) {
					$this->getSubscription()->unsetData($key);
				}
				else {
					$this->getSubscription()->setData($key, sprintf('%.4F', $this->getSubscription()->getData($key)));
				}
			}
		}
		
		// automatically determine start date, if not set
		if ($this->getSubscription()->getStartDatetime()) {
			$startDate = Mage::helper('customweb_subscription')->toDateObject($this->getSubscription()->getStartDatetime());
			$this->setNearestStartDatetime($startDate);
		}
		else {
			$this->setNearestStartDatetime();
		}
		
		return $this->getSubscription();
	}

	/**
	 * Recursively cleanup array from objects.
	 *
	 * @param array &$array
	 */
	private function _cleanupArray(&$array){
		if (!$array) {
			return;
		}
		foreach ($array as $key => $value) {
			if (is_object($value)) {
				unset($array[$key]);
			}
			elseif (is_array($value)) {
				$this->_cleanupArray($array[$key]);
			}
		}
	}

	/**
	 * Generate a new reference id for this subscription.
	 *
	 * @param int $id
	 * @return string
	 */
	private function _getNewReferenceId($id){
		$incrementInstance = Mage::getModel('eav/entity_increment_numeric')->setPrefix($this->getSubscription()->getStoreId())->setPadLength(8)->setPadChar(
				'0');
		return $incrementInstance->format($id);
	}
}