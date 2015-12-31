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
 * Contains order related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Order extends Customweb_Subscription_Model_Subscription_Abstract {

	/**
	 *
	 * @var Mage_Sales_Model_Order
	 */
	private $_lastOrder = null;

	/**
	 *
	 * @var Mage_Sales_Model_Order
	 */
	private $_initialOrder = null;

	/**
	 * Load subscription by order
	 *
	 * @param int $orderId
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function loadByOrder($orderId){
		$link = Mage::getModel('customweb_subscription/order')->load($orderId, 'order_id');
		if ($link->getLinkId()) {
			return Mage::getModel('customweb_subscription/subscription')->load($link->getSubscriptionId());
		}
		return null;
	}

	/**
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getLastOrder(){
		if ($this->_lastOrder == null) {
			if ($this->getSubscription()->getLastOrderId() != null) {
				$this->_lastOrder = Mage::getModel('sales/order')->load($this->getSubscription()->getLastOrderId());
			}
			else {
				$this->_lastOrder = false;
			}
		}
		return $this->_lastOrder;
	}

	/**
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getInitialOrder(){
		if ($this->_initialOrder == null) {
			$this->_initialOrder = Mage::getModel('sales/order')->load($this->getSubscription()->getInitialOrderId());
		}
		return $this->_initialOrder;
	}

	/**
	 * Return the ids of all orders related to this subscription.
	 *
	 * @return array
	 */
	public function getChildOrderIds(){
		return $this->_getResource()->getChildOrderIds($this->getSubscription());
	}

	/**
	 * Return all orders related to this subscription.
	 *
	 * @return Mage_Sales_Model_Resource_Order_Collection
	 */
	public function getChildOrders(){
		return Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('entity_id', array(
			'in' => $this->getChildOrderIds()
		));
	}

	/**
	 * Check whether the subscription has an open order.
	 *
	 * @return boolean
	 */
	public function hasOpenOrder(){
		if (!$this->getSubscription()->isMethodSuspendOnPendingPayment()) {
			return false;
		}
		return $this->getOpenOrder() !== null;
	}

	/**
	 * Return the latest order related to this subscription.
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOpenOrder(){
		$order = $this->getSubscription()->getLastOrder();
		if ($order !== false) {
			if ($order->getTotalDue() > 0 && in_array($order->getState(),
					array(
						Mage_Sales_Model_Order::STATE_NEW,
						Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
						Mage_Sales_Model_Order::STATE_PROCESSING
					))) {
				return $order;
			}
		}
		return null;
	}

	/**
	 * Initialize new order based on subscription data.
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function createOrder(){
		try {
			$items = $this->_getItems();

			if (!$this->getSubscription()->isSalable()) {
				$this->getSubscription()->suspend();
				throw new Exception('Not all products needed by subscription #' . $this->getSubscription()->getReferenceId() . ' are available.');
			}

			if ($this->getSubscription()->getMethodInstance() instanceof Customweb_Payment_Authorization_IPaymentMethod) {
				$this->getSubscription()->getMethodInstance()->getHelper()->setConfigurationStoreId($this->getSubscription()->getStoreId());
			}

			if ($this->getSubscription()->getCalculatePrice()) {
				Mage::getSingleton('adminhtml/session_quote')->clear()->resetVariables();
				$orderCreateModel = Mage::getModel('adminhtml/sales_order_create');
				$initialOrder = $this->getInitialOrder();
				$initialOrder->setReordered(true);
				$initialOrder->setCouponCode(null);
				Mage::getSingleton('adminhtml/session_quote')->setUseOldShippingMethod(true);
				$orderCreateModel->initFromOrder($initialOrder);
				$quote = $orderCreateModel->getQuote();
				$order = $orderCreateModel->setIsValidate(true)->setShippingMethod($initialOrder->getShippingMethod())->createOrder();
				Mage::getSingleton('adminhtml/session_quote')->clear()->resetVariables();
			}
			else {
				$billingAmount = $this->getSubscription()->getBillingAmount();
				$shippingAmount = $this->getSubscription()->getShippingAmount();
				$taxAmount = $this->getSubscription()->getTaxAmount();
				$grandTotal = $this->getSubscription()->getGrandTotal();

				$weight = 0;
				$isVirtual = 1;
				foreach ($items as $item) {
					$weight += $item->getWeight();
					if (!$item->getIsVirtual()) {
						$isVirtual = 0;
					}
				}

				$order = Mage::getModel('sales/order');

				$billingInfo = $this->getSubscription()->getBillingAddressInfo();
				$billingAddress = Mage::getModel('sales/order_address')->setData($billingInfo)->setId(null);

				$shippingInfo = $this->getSubscription()->getShippingAddressInfo();
				$shippingAddress = Mage::getModel('sales/order_address')->setData($shippingInfo)->setId(null);

				$payment = Mage::getModel('sales/order_payment')->setMethod($this->getSubscription()->getMethodCode());

				$transferDataKeys = array(
					'store_id',
					'store_name',
					'customer_id',
					'customer_email',
					'customer_firstname',
					'customer_lastname',
					'customer_middlename',
					'customer_prefix',
					'customer_suffix',
					'customer_taxvat',
					'customer_gender',
					'customer_is_guest',
					'customer_note_notify',
					'customer_group_id',
					'customer_note',
					'shipping_method',
					'shipping_description',
					'base_currency_code',
					'global_currency_code',
					'order_currency_code',
					'store_currency_code',
					'base_to_global_rate',
					'base_to_order_rate',
					'store_to_base_rate',
					'store_to_order_rate'
				);

				$orderInfo = $this->getSubscription()->getOrderInfo();
				foreach ($transferDataKeys as $key) {
					if (isset($orderInfo[$key])) {
						$order->setData($key, $orderInfo[$key]);
					}
					elseif (isset($shippingInfo[$key])) {
						$order->setData($key, $shippingInfo[$key]);
					}
				}

				$order->setStoreId($this->getSubscription()->getStoreId())->setState(Mage_Sales_Model_Order::STATE_NEW, true)->setBaseToOrderRate(
						$this->getSubscription()->getInfoValue('order_info', 'base_to_order_rate'))->setStoreToOrderRate(
						$this->getSubscription()->getInfoValue('order_info', 'store_to_order_rate'))->setOrderCurrencyCode(
						$this->getSubscription()->getInfoValue('order_info', 'order_currency_code'))->setBaseSubtotal($billingAmount)->setSubtotal(
						$billingAmount)->setBaseShippingAmount($shippingAmount)->setShippingAmount($shippingAmount)->setBaseTaxAmount($taxAmount)->setTaxAmount(
						$taxAmount)->setBaseGrandTotal($grandTotal)->setGrandTotal($grandTotal)->setIsVirtual($isVirtual)->setWeight($weight)->setTotalQtyOrdered(
						$this->getSubscription()->getInfoValue('order_info', 'items_qty'))->setBillingAddress($billingAddress)->setShippingAddress(
						$shippingAddress)->setPayment($payment);

				foreach ($items as $item) {
					$order->addItem($item);
				}

				$transaction = Mage::getModel('core/resource_transaction');
				$transaction->addObject($order);
				$transaction->addCommitCallback(array($order, 'place'));
				$transaction->addCommitCallback(array($order, 'save'));
				$transaction->save();

				$orderCreateModel = Mage::getModel('adminhtml/sales_order_create');
				$orderCreateModel->initFromOrder($order);
				$quote = $orderCreateModel->getQuote();
				Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
			}
			Mage::unregister('rule_data');
			return $order;
		} catch(Exception $e) {
			Mage::unregister('rule_data');
			throw $e;
		}
	}

	/**
	 * Add order relation to subscription.
	 *
	 * @param int $orderId
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function addOrderRelation($orderId){
		$this->_getResource()->addOrderRelation($this->getSubscription()->getId(), $orderId);
		$this->getSubscription()->setLastOrderId($orderId)->save();
		return $this->getSubscription();
	}

	/**
	 * Create and return new order item based on subscription item data
	 * for regular payment.
	 *
	 * @return array
	 */
	private function _getItems(){
		$items = array();
		foreach ($this->getSubscription()->getOrderItemInfo() as $orderItemInfo) {
			$items[] = Mage::getModel('sales/order_item')->setData($orderItemInfo)->setQtyInvoiced(0)->setQtyRefunded(0)->setQtyShipped(0)->setQtyCanceled(
					0)->setId(null);
		}
		return $items;
	}
}