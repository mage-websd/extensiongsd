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
 * Represents a subscription.
 *
 * @author Simon Schurter
 *
 * @method int getId()
 * @method string getStatus()
 * @method Customweb_Subscription_Model_Subscription setStatus(string $value)
 * @method int getCustomerId()
 * @method Customweb_Subscription_Model_Subscription setCustomerId(int $value)
 * @method int getStoreId()
 * @method Customweb_Subscription_Model_Subscription setStoreId(int $value)
 * @method string getMethodCode()
 * @method Customweb_Subscription_Model_Subscription setMethodCode(string $value)
 * @method string getCreatedAt()
 * @method Customweb_Subscription_Model_Subscription setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Customweb_Subscription_Model_Subscription setUpdatedAt(string $value)
 * @method string getReferenceId()
 * @method Customweb_Subscription_Model_Subscription setReferenceId(string $value)
 * @method string getSubscriberName()
 * @method Customweb_Subscription_Model_Subscription setSubscriberName(string $value)
 * @method string getDescription()
 * @method Customweb_Subscription_Model_Subscription setDescription(string $value)
 * @method string getStartDatetime()
 * @method Customweb_Subscription_Model_Subscription setStartDatetime(string $value)
 * @method string getLastDatetime()
 * @method Customweb_Subscription_Model_Subscription setLastDatetime(string $value)
 * @method string getLastRegularDatetime()
 * @method Customweb_Subscription_Model_Subscription setLastRegularDatetime(string $value)
 * @method int getLastOrderId()
 * @method Customweb_Subscription_Model_Subscription setLastOrderId(int $value)
 * @method string getPeriodUnit()
 * @method Customweb_Subscription_Model_Subscription setPeriodUnit(string $value)
 * @method int getPeriodFrequency()
 * @method Customweb_Subscription_Model_Subscription setPeriodFrequency(int $value)
 * @method int getPeriodMaxCycles()
 * @method Customweb_Subscription_Model_Subscription setPeriodMaxCycles(int $value)
 * @method double getBillingAmount()
 * @method Customweb_Subscription_Model_Subscription setBillingAmount(double $value)
 * @method string getCurrencyCode()
 * @method Customweb_Subscription_Model_Subscription setCurrencyCode(string $value)
 * @method double setShippingAmount()
 * @method Customweb_Subscription_Model_Subscription setShippingAmount(double $value)
 * @method double getTaxAmount()
 * @method Customweb_Subscription_Model_Subscription setTaxAmount(double $value)
 * @method double getInitAmount()
 * @method Customweb_Subscription_Model_Subscription setInitAmount(double $value)
 * @method boolean getCancelRequest()
 * @method Customweb_Subscription_Model_Subscription setCancelRequest(boolean $value)
 * @method string getCancelDate()
 * @method Customweb_Subscription_Model_Subscription setCancelDate(string $value)
 * @method int getCancelPeriod()
 * @method Customweb_Subscription_Model_Subscription setCancelPeriod(int $value)
 * @method string getLinkHash()
 * @method Customweb_Subscription_Model_Subscription setLinkHash(string $value)
 * @method int getPaymentId()
 * @method Customweb_Subscription_Model_Subscription setPaymentId(int $value)
 * @method string getOrderInfo()
 * @method Customweb_Subscription_Model_Subscription setOrderInfo(string $value)
 * @method string getOrderItemInfo()
 * @method Customweb_Subscription_Model_Subscription setOrderItemInfo(string $value)
 * @method string getBillingAddressInfo()
 * @method Customweb_Subscription_Model_Subscription setBillingAddressInfo(string $value)
 * @method string getShippingAddressInfo()
 * @method Customweb_Subscription_Model_Subscription setShippingAddressInfo(string $value)
 * @method boolean getCalculatePrice()
 * @method Customweb_Subscription_Model_Subscription setCalculatePrice(boolean $value)
 * @method int getInitialOrderId()
 * @method Customweb_Subscription_Model_Subscription setInitialOrderId(int $value)
 * @method boolean getCanCustomerSuspend()
 * @method Customweb_Subscription_Model_Subscription setCanCustomerSuspend(boolean $value)
 * @method int getCancelCount()
 * @method Customweb_Subscription_Model_Subscription setCancelCount(int $value)
 */
class Customweb_Subscription_Model_Subscription extends Mage_Core_Model_Abstract implements Customweb_Subscription_Model_ISubscription {

	/**
	 * Event prefix and object
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'customweb_subscription_subscription';
	protected $_eventObject = 'subscription';

	/**
	 *
	 * @var Customweb_Subscription_Model_Subscription_Abstract[]
	 */
	private $_extensions = array();

	/**
	 *
	 * @var Customweb_Subscription_Model_Logger
	 */
	private $_logger = null;

	/**
	 *
	 * @var Mage_Customer_Model_Customer
	 */
	private $_customer = null;

	/**
	 *
	 * @var Mage_Core_Model_Store
	 */
	private $_store = null;

	/**
	 *
	 * @var Mage_Payment_Model_Method_Abstract
	 */
	protected $_methodInstance = null;

	/**
	 *
	 * @var Mage_Sales_Model_Order_Payment
	 */
	private $_payment = null;

	/**
	 *
	 * @var Customweb_Subscription_Model_Plan
	 */
	private $_plan = null;

	protected function _construct(){
		$this->_init('customweb_subscription/subscription');

		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Cancel($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Create($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Error($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Failure($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Order($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Payment($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Reminder($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Schedule($this);
		$this->_extensions[] = new Customweb_Subscription_Model_Subscription_Suspend($this);

		$this->setVersion(Customweb_Subscription_Model_Resource_Migration::CURRENT_VERSION);
	}

	public function __call($method, $arguments){
		foreach ($this->_extensions as $extension) {
			if (method_exists($extension, $method) && is_callable(array(
				$extension,
				$method
			))) {
				return call_user_func_array(array(
					$extension,
					$method
				), $arguments);
			}
		}
		return parent::__call($method, $arguments);
	}

	/**
	 * Load subscription by reference identifier
	 *
	 * @param string $referenceId
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function loadByReferenceId($referenceId){
		return $this->load($referenceId, 'reference_id');
	}

	/**
	 * @return Customweb_Subscription_Model_Logger
	 */
	private function getLogger(){
		if ($this->_logger == null) {
			$this->_logger = new Customweb_Subscription_Model_Logger($this);
		}
		return $this->_logger;
	}

	/**
	 * Return the customer associated with this subscription.
	 *
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer(){
		if ($this->_customer == null) {
			$this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
		}
		return $this->_customer;
	}

	/**
	 * Return the store associated with this subscription.
	 *
	 * @return Mage_Core_Model_Store
	 */
	public function getStore(){
		if ($this->_store == null) {
			$this->_store = Mage::getModel('core/store')->load($this->getStoreId());
		}
		return $this->_store;
	}

	/**
	 * Return the payment method instance.
	 *
	 * @return Mage_Payment_Model_Method_Abstract
	 */
	public function getMethodInstance(){
		if ($this->_methodInstance == null && $this->getMethodCode() != null) {
			$this->setMethodInstance(Mage::helper('payment')->getMethodInstance($this->getMethodCode()));
		}
		if ($this->_methodInstance != null) {
			$this->_methodInstance->setStore($this->getStoreId());
		}
		return $this->_methodInstance;
	}

	/**
	 * Setter for payment method instance.
	 *
	 * @param Mage_Payment_Model_Method_Abstract $object
	 * @return Customweb_Subscription_Model_Subscription
	 * @throws Exception
	 */
	public function setMethodInstance(Mage_Payment_Model_Method_Abstract $object){
		if (Mage::helper('customweb_subscription/payment')->isPaymentMethodEnabled($object)) {
			$this->_methodInstance = $object;
			$this->setMethodCode($object->getCode());
		}
		else {
			throw new Exception('Invalid payment method instance for use in subscription.');
		}
		return $this;
	}

	/**
	 *
	 * @return Mage_Sales_Model_Order_Payment
	 */
	public function getPayment(){
		if ($this->_payment == null) {
			$this->_payment = Mage::getModel('sales/order_payment')->load($this->getPaymentId());
		}
		return $this->_payment;
	}

	/**
	 * Return the number of cycles this subscription has run.
	 *
	 * @return int
	 */
	public function getNumberOfCycles(){
		$number = 0;
		foreach ($this->getChildOrders()->getItems() as $order) {
			if (!$order->isCanceled()) {
				$number++;
			}
		}
		return $number;
	}

	/**
	 * Returns the plan instance.
	 *
	 * @return Customweb_Subscription_Model_Plan
	 */
	public function getPlan(){
		if ($this->_plan == null) {
			$this->_plan = Mage::getModel('customweb_subscription/plan')->fromSubscription($this);
		}
		return $this->_plan;
	}

	/**
	 * Check if the subscription status is unknown.
	 *
	 * @return boolean
	 */
	public function isUnknown(){
		return $this->getStatus() == self::STATUS_UNKNOWN;
	}

	/**
	 * Check if the subscription is active.
	 *
	 * @return boolean
	 */
	public function isActive(){
		return $this->getStatus() == self::STATUS_ACTIVE;
	}

	/**
	 * Check whether the workflow allows to activate the subscription.
	 *
	 * @return boolean
	 */
	public function canActivate(){
		return $this->checkWorkflow(self::STATUS_ACTIVE) && !$this->isPaid() && !$this->isPending() && !$this->isAuthorized();
	}

	/**
	 * Activate the subscription if allowed.
	 */
	public function activate(){
		$this->checkWorkflow(self::STATUS_ACTIVE, false);
		$this->setStatus(self::STATUS_ACTIVE)->save();
		$this->scheduleNextJobs();
	}

	/**
	 * Check if the subscription is pending.
	 *
	 * @return boolean
	 */
	public function isPending(){
		return $this->getStatus() == self::STATUS_PENDING;
	}

	/**
	 * Check if the subscription is expired.
	 *
	 * @return boolean
	 */
	public function isExpired(){
		return $this->getStatus() == self::STATUS_EXPIRED;
	}

	/**
	 * Should the subscription be suspended when a payment is missing?
	 *
	 * @return boolean
	 */
	public function isMethodSuspendOnPendingPayment(){
		if ($this->getMethodInstance()->getConfigData('suspend_on_pending_payment') === null) {
			return true;
		}
		return $this->getMethodInstance()->getConfigData('suspend_on_pending_payment');
	}

	/**
	 * Return grand total amount of this subscription.
	 *
	 * @return float
	 */
	public function getGrandTotal(){
		return $this->getBillingAmount() + $this->getShippingAmount() + $this->getTaxAmount();
	}

	/**
	 * Check if all products in this subscription are salable.
	 *
	 * @return boolean
	 */
	public function isSalable(){
		foreach ($this->getItems() as $item) {
			$product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())->load($item->getProductId());
			if (!$item->getHasChildren() && !$product->isSalable()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Getter for additional information value.
	 * It is assumed that the specified additional info is an object or
	 * associative array.
	 *
	 * @param string $infoKey
	 * @param string $infoValueKey
	 * @return mixed|null
	 */
	public function getInfoValue($infoKey, $infoValueKey){
		$info = $this->getData($infoKey);
		if (!$info) {
			return;
		}
		if (!is_object($info)) {
			if (is_array($info) && isset($info[$infoValueKey])) {
				return $info[$infoValueKey];
			}
		}
		else {
			if ($info instanceof Varien_Object) {
				return $info->getDataUsingMethod($infoValueKey);
			}
			elseif (isset($info->$infoValueKey)) {
				return $info->$infoValueKey;
			}
		}
	}

	/**
	 * Get all items of this subscription.
	 *
	 * @return array
	 */
	public function getItems(){
		if ($this->_items == null) {
			$items = array();
			foreach ($this->getOrderItemInfo() as $orderItemInfo) {
				$item = Mage::getModel('sales/order_item')->setData($orderItemInfo);
				$items[$item->getId()] = $item;
			}

			foreach ($items as $item) {
				if ($item->getParentItemId()) {
					$item->setParentItem($items[$item->getParentItemId()]);
				}
			}
			$this->_items = $items;
		}

		return $this->_items;
	}

	/**
	 * Check whether subscription can be changed to specified status.
	 *
	 * @param string $againstStatus
	 * @param boolean $soft
	 * @return boolean
	 * @throws Mage_Core_Exception
	 */
	public function checkWorkflow($againstStatus, $soft = true){
		$this->_initWorkflow();
		$status = $this->getStatus();
		$result = (!empty($this->_workflow[$status])) && in_array($againstStatus, $this->_workflow[$status]);
		if (!$soft && !$result) {
			Mage::throwException(
					Mage::helper('customweb_subscription')->__('This subscription status cannot be changed from <em>%s</em> to <em>%s</em>.', $status,
							$againstStatus));
		}
		return $result;
	}

	/**
	 * Send a new email.
	 *
	 * @param string $template
	 * @param string $sender
	 * @param array $templateParams
	 * @param string $storeId
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null){
		$mailer = Mage::getModel('core/email_template_mailer');
		$emailInfo = Mage::getModel('core/email_info');

		if ($this->getCustomerId() != null) {
			$customer = $this->getCustomer();
			$emailInfo->addTo($customer->getEmail(), $customer->getName());
		}
		else {
			$emailInfo->addTo($this->getInfoValue('billing_address_info', 'email'), $this->getSubscriberName());
		}
		$mailer->addEmailInfo($emailInfo);

		$mailer->setSender($sender);
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId($template);
		$mailer->setTemplateParams($templateParams);
		$mailer->send();
		return $this;
	}

	/**
	 * Initialize the workflow reference.
	 */
	private function _initWorkflow(){
		if (null === $this->_workflow) {
			$this->_workflow = array(
				'unknown' => array(
					'pending',
					'active',
					'canceled'
				),
				'pending' => array(
					'active',
					'paid',
					'canceled',
					'suspended'
				),
				'active' => array(
					'suspended',
					'canceled',
					'paid'
				),
				'suspended' => array(
					'active',
					'canceled'
				),
				'canceled' => array(),
				'failed' => array(
					'active',
					'canceled'
				),
				'error' => array(
					'active',
					'canceled'
				),
				'expired' => array(),
				'paid' => array(
					'active'
				)
			);
		}
	}
}