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
 * Contains payment related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Payment extends Customweb_Subscription_Model_Subscription_Abstract {

	/**
	 * Email template config paths
	 */
	const XML_PATH_EMAIL_UPDATE = 'customweb_subscription/email/template_update';
	const XML_PATH_EMAIL_PENDING = 'customweb_subscription/email/template_pending';

	/**
	 * Return the transaction of the payment method.
	 *
	 * @return Customweb_Payment_Authorization_ITransaction
	 */
	public function getPaymentTransaction(){
		return $this->getSubscription()->getMethodInstance()->getHelper()->loadTransactionByPayment($this->getSubscription()->getPaymentId());
	}

	/**
	 * Check if the subscription is paid.
	 *
	 * @return boolean
	 */
	public function isAuthorized(){
		return $this->getStatus() == self::STATUS_AUTHORIZED;
	}

	/**
	 * Check if the subscription is paid.
	 *
	 * @return boolean
	 */
	public function isPaid(){
		return $this->getStatus() == self::STATUS_PAID;
	}

	/**
	 * Check whether the workflow allows to authorize the subscription.
	 *
	 * @return boolean
	 */
	public function canAuthorize(){
		return $this->getSubscription()->isActive() && !$this->getSubscription()->hasOpenOrder();
	}

	/**
	 * Check whether the workflow allows to pay the subscription.
	 *
	 * @return boolean
	 */
	public function canPay(){
		return $this->getSubscription()->isPending() && !$this->getSubscription()->hasOpenOrder();
	}

	/**
	 * Can subscription be paid online in user account.
	 *
	 * @return boolean
	 */
	public function canPayOnline(){
		return $this->getSubscription()->getMethodCode() != 'subscription_invoice' &&
				 $this->getSubscription()->getMethodCode() != 'subscription_prepayment';
	}

	/**
	 * Can request a manual payment.
	 *
	 * @param Zend_Date $manualDueDate
	 * @return boolean
	 */
	public function canRequestPaymentManually($manualDueDate = null){
		if ($manualDueDate == null) {
			$manualDueDate = Zend_Date::now();
		}
		try {
			$dueDate = $this->getSubscription()->getPlan()->getNextDueDate();
		}
		catch (Exception $e) {
			$dueDate = null;
		}
		if ($dueDate !== null) {
			$checkDate = $this->getSubscription()->getCheckDate($manualDueDate);
			if (!$checkDate->isEarlier($dueDate)) {
				return false;
			}
		}
		return true;
	}

	public function scheduleManualPayment(){
		Mage::getModel('customweb_subscription/schedule')->getResource()->deleteManualBySubscription($this->getSubscription()->getId());
		$this->getSubscription()->deletePendingJobs();

		$manualDueDate = Zend_Date::now();
		if (!$this->canRequestPaymentManually($manualDueDate)) {
			throw new Exception('The subscription cannot be authorized manually.');
		}
		$scheduler = Mage::getModel('customweb_subscription/scheduler');
		$scheduler->createJob($this->getSubscription(), $manualDueDate, 'pay_manual');
		$scheduler->createJob($this->getSubscription(), $this->getSubscription()->getCheckDate($manualDueDate), 'check_manual', Mage::helper('customweb_subscription')->toDateString($manualDueDate));
	}

	/**
	 * Request a new payment.
	 *
	 * @throws Exception
	 */
	public function requestPayment(){
		if ($this->getSubscription()->getMethodInstance() instanceof Customweb_Payment_Authorization_IPaymentMethod) {
			$payment = new Customweb_Subscription_Model_Method($this->getSubscription()->getMethodInstance());
			if ($payment->isSupportingRecurring()) {
				$this->authorize();
			}
			else {
				$this->sendPaymentEmail();
				$this->setStatus(self::STATUS_PENDING);
				$this->save();
			}
		}
		else {
			if (!$this->canAuthorize()) {
				throw new Exception('The subscription cannot be authorized.');
			}

			$this->_getResource()->beginTransaction();
			try {
				$order = $this->createOrder();
				Mage::register('customweb_subscription_recurring_order', true);
				$order->sendNewOrderEmail();
				Mage::unregister('customweb_subscription_recurring_order');

				$this->setStatus(self::STATUS_PENDING);
				$this->save();
				$this->_getResource()->commit();
			}
			catch (Exception $e) {
				Mage::unregister('customweb_subscription_recurring_order');
				$this->_getResource()->rollBack();
				throw $e;
			}
		}
	}

	/**
	 * Create new order and payment for this subscription.
	 *
	 * @throws Exception
	 */
	public function authorize(){
		$payment = new Customweb_Subscription_Model_Method($this->getSubscription()->getMethodInstance());
		if (!$payment->isSupportingRecurring()) {
			throw new Exception('The payment method does not support recurring authorization.');
		}

		$schedule = Mage::registry('customweb_subscription_schedule');

		$storedOrderId = $schedule->getAdditionalData();
		$order = null;
		$transaction = null;
		if (!empty($storedOrderId)) {
			$order = Mage::getModel('sales/order')->load($storedOrderId);
			if ($order != null && $order->getId()) {
				try {
					$transaction = $payment->getHelper()->loadTransactionByOrder($order->getId());
				}
				catch (Exception $e) {
				}
			}
		}
		if ($transaction == null || !($transaction->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction) || !$transaction->getTransactionObject()->isAuthorized()) {
			if ($order != null && !$order->isCanceled()) {
				$order->cancel()->save();
			}

			$this->_getResource()->beginTransaction();
			try {
				Mage::getSingleton('core/session')->setSubscriptionId($this->getSubscription()->getId());
				$order = $this->createOrder();
				Mage::getSingleton('core/session')->setSubscriptionOrder($order->getId());

				$transaction = $this->createTransaction($order);
				$schedule->setAdditionalData($order->getId())->save();
				$this->_getResource()->commit();
			}
			catch (Exception $e) {
				$this->_getResource()->rollBack();
				throw $e;
			}
		}

		if ($transaction == null || !($transaction->getTransactionObject() instanceof Customweb_Payment_Authorization_ITransaction)) {
			throw new Exception('The payment transaction has not been created correctly.');
		}

		if (!$transaction->getTransactionObject()->isAuthorized()) {
			$this->_getResource()->beginTransaction();
			try {
				$payment = new Customweb_Subscription_Model_Method($order->getPayment()->getMethodInstance(), $this->getSubscription());
				Mage::register('customweb_subscription_recurring_order', true);
				$transaction = $payment->authorizeSubscription($transaction);
				Mage::unregister('customweb_subscription_recurring_order');
				$this->_getResource()->commit();
			}
			catch (Exception $e) {
				Mage::unregister('customweb_subscription_recurring_order');
				$this->_getResource()->rollBack();
				if ($schedule->getCount() < Customweb_Subscription_Model_Scheduler::RETRIES) {
					throw $e;
				}
				Mage::logException($e);
				if (!$order->isCanceled()) {
					$order->cancel()->save();
					$this->sendPaymentEmail(true);
				}
			}
		}
	}

	/**
	 *
	 * @param Object $transaction
	 */
	public function updateStatusByTransaction($transaction){
		$order = $transaction->getOrder();
		if ($transaction != null && $transaction->getTransactionObject() != null && $this->getSubscription()->isActive() && $order != null &&
				 $this->getSubscription()->hasOpenOrder() && $order->getId() == $this->getSubscription()->getOpenOrder()->getId()) {
			if ($transaction->getTransactionObject()->isCaptured()) {
				$this->setStatus(self::STATUS_PAID);
			}
			elseif ($transaction->getTransactionObject()->isAuthorized()) {
				$this->setStatus(self::STATUS_AUTHORIZED);
			}
			$this->save();
		}
	}

	/**
	 * Update the subscription after capturing the related payment.
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
	public function afterCapture(Mage_Sales_Model_Order $order){
		if (!$this->getSubscription()->isPending() && !$this->isAuthorized()) {
			return;
		}

		if ($this->getSubscription()->getMethodInstance() instanceof Customweb_Payment_Authorization_IPaymentMethod) {
			$payment = new Customweb_Subscription_Model_Method($order->getPayment()->getMethodInstance(), $this->getSubscription());
			$transaction = $payment->getHelper()->loadTransactionByOrder($order->getId());

			if ($transaction->getTransactionObject()->isCaptured()) {
				$this->setStatus(self::STATUS_PAID);
				$this->save();
			}
		}
		else {
			$this->setStatus(self::STATUS_PAID);
			$this->save();
		}

		$this->getLogger()->info('The subscription was captured.');
	}

	/**
	 * Check whether the subscription's last order has been correctly paid.
	 */
	public function validatePayment($dueDate = null, $manual = false){
		if ($this->getSubscription()->getPeriodMaxCycles() != null &&
				 $this->getSubscription()->getNumberOfCycles() >= $this->getSubscription()->getPeriodMaxCycles()) {
			$this->setStatus(self::STATUS_EXPIRED)->save();
			$this->getSubscription()->deletePendingJobs();
			$this->getLogger()->info('The subscription was moved to expired.');
		}
		elseif (($this->isPaid() || !$this->getSubscription()->isMethodSuspendOnPendingPayment())) {
			if ($dueDate !== null) {
				$this->getSubscription()->setLastDatetime($dueDate);
				if (!$manual) {
					$this->getSubscription()->setLastRegularDatetime($dueDate);
				}
			}
			$this->getSubscription()->activate();
			$this->getLogger()->info('The payment check was successful.');
		}
		elseif ($this->getSubscription()->isPending() || $this->isAuthorized()) {
			$this->getSubscription()->markAsFailed();
			$this->getLogger()->error('The subscription failed because it was not captured on validation.');
		}
		else {
			throw new Exception('The subscription\'s payment cannot be validated.');
		}
	}

	/**
	 * Send a new payment email to the customer.
	 *
	 * @param boolean $update
	 * @throws Exception
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function sendPaymentEmail($update = false){
		if (!$this->canAuthorize()) {
			throw new Exception('Payment cannot be requested.');
		}

		$storeId = $this->getSubscription()->getStoreId();

		if ($this->getSubscription()->getCustomerId() != null) {
			$customerName = $this->getSubscription()->getCustomer()->getName();
		}
		else {
			$customerName = $this->getSubscription()->getSubscriberName();
		}

		if ($update) {
			$templateConfig = self::XML_PATH_EMAIL_UPDATE;
		}
		else {
			$templateConfig = self::XML_PATH_EMAIL_PENDING;
		}
		$template = Mage::getStoreConfig($templateConfig, $storeId);

		$sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId);

		$appEmulation = Mage::getSingleton('core/app_emulation');
		$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		try {
			$subscriptionCreatedAt = Mage::helper('customweb_subscription')->formatDate($this->getSubscription()->getCreatedAt(), 'medium', true);
		}
		catch (Exception $exception) {
			// Stop store emulation process
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			throw $exception;
		}
		$subscriptionPeriod = Mage::helper('customweb_subscription/render')->renderPlan($this->getSubscription()->getPlan());
		$subscriptionEnd = Mage::helper('customweb_subscription/render')->renderPlanEnd($this->getSubscription()->getPlan());
		$cancelPeriod = Mage::helper('customweb_subscription')->__('Repeats %s time(s) after cancelation request.',
				$this->getSubscription()->getCancelPeriod());
		$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

		$this->getSubscription()->sendEmailTemplate($template, $sender,
				array(
					'subscription' => $this->getSubscription(),
					'customer_name' => $customerName,
					'link' => $this->getPaymentLink(),
					'subscription_created_at' => $subscriptionCreatedAt,
					'subscription_period' => $subscriptionPeriod,
					'subscription_end' => $subscriptionEnd,
					'cancel_period' => $cancelPeriod
				), $storeId);

		return $this->getSubscription();
	}

	/**
	 * Return the current payment link for this subscription.
	 *
	 * @return string
	 */
	public function getPaymentLink(){
		if (!$this->getSubscription()->isPending() || $this->getSubscription()->getLinkHash() == null) {
			$this->getSubscription()->setLinkHash($this->generateHash());
			$this->save();
		}

		return $this->getSubscription()->getStore()->getUrl('subscription/payment/index',
				array(
					'subscription_id' => $this->getSubscription()->getId(),
					'key' => $this->getSubscription()->getLinkHash()
				));
	}

	/**
	 * Generate a new payment link for this subscription.
	 *
	 * @return string
	 */
	protected function generateHash(){
		$chars = Mage_Core_Helper_Data::CHARS_PASSWORD_LOWERS . Mage_Core_Helper_Data::CHARS_PASSWORD_DIGITS;
		return Mage::helper('core')->getRandomString(32, $chars);
	}

	/**
	 * Create a new order for the transaction.
	 *
	 * @return Mage_Sales_Model_Order
	 */
	private function createOrder(){
		try {
			Mage::register('customweb_subscription_recurring_order', true);
			$order = $this->getSubscription()->createOrder();
			$this->getSubscription()->addOrderRelation($order->getId());
			Mage::unregister('customweb_subscription_recurring_order');
			return $order;
		} catch(Exception $e) {
			Mage::unregister('customweb_subscription_recurring_order');
			throw $e;
		}
	}

	/**
	 * Get or create a transaction for the given order.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return Object
	 */
	private function createTransaction(Mage_Sales_Model_Order $order){
		$payment = new Customweb_Subscription_Model_Method($order->getPayment()->getMethodInstance(), $this->getSubscription());
		try {
			$transaction = $payment->getHelper()->loadTransactionByOrder($order->getId());
		}
		catch (Exception $e) {
			$transaction = $payment->createTransaction($order);
		}
		return $transaction;
	}
}