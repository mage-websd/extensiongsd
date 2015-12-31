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
 * Wrapper for the payment method to add custom behaviour.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Method implements Customweb_Payment_Authorization_IPaymentMethod {
	
	/**
	 * Original payment method instance.
	 *
	 * @var Customweb_Payment_Authorization_IPaymentMethod
	 */
	private $_paymentMethod = null;
	
	/**
	 * The subscription model.
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	private $_subscription = null;

	/**
	 *
	 * @param Customweb_Payment_Authorization_IPaymentMethod $paymentMethod
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 */
	public function __construct(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, Customweb_Subscription_Model_Subscription $subscription = null){
		$this->_paymentMethod = $paymentMethod;
		$this->_subscription = $subscription;
	}

	/**
	 * Redirect method calls to the original transaction context.
	 *
	 * @param string $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments){
		return call_user_func_array(array(
			$this->_paymentMethod,
			$name 
		), $arguments);
	}

	public function getPaymentMethodName(){
		return $this->_paymentMethod->getPaymentMethodName();
	}

	public function getPaymentMethodDisplayName(){
		return $this->_paymentMethod->getPaymentMethodDisplayName();
	}

	public function getPaymentMethodConfigurationValue($key, $languageCode = null){
		return $this->_paymentMethod->getPaymentMethodConfigurationValue($key, $languageCode = null);
	}

	public function existsPaymentMethodConfigurationValue($key, $languageCode = null){
		return $this->_paymentMethod->existsPaymentMethodConfigurationValue($key, $languageCode = null);
	}

	/**
	 * Update the subscription after processing this payment method.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param array $parameters
	 */
	public function afterProcess(Mage_Sales_Model_Order $order, array $parameters){
		if ($this->isSupportingRecurring()) {
			$this->_subscription->setPaymentId($order->getPayment()->getId());
		}
		
		$transaction = $this->getHelper()->loadTransactionByOrder($order->getId());
		
		if ($transaction->getTransactionObject()->isAuthorized()) {
			if ($transaction->getTransactionObject()->isCaptured()) {
				$this->_subscription->setStatus(Customweb_Subscription_Model_Subscription::STATUS_PAID);
			}
			else {
				$this->_subscription->setStatus(Customweb_Subscription_Model_Subscription::STATUS_AUTHORIZED);
			}
		}
		else {
			$this->_subscription->setStatus(Customweb_Subscription_Model_Subscription::STATUS_PENDING);
		}
		$this->_subscription->save();
	}

	/**
	 * Authorize the subscription.
	 *
	 * @param Object $transaction
	 * @throws Exception
	 * @return Object
	 */
	public function authorizeSubscription($transaction){
		$adapter = $this->getHelper()->getAuthorizationAdapter(Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
		try {
			$adapter->process($transaction->getTransactionObject());
		}
		catch (Exception $e) {
			$transaction->save();
			throw $e;
		}
		$transaction->save();
		return $transaction;
	}

	/**
	 * Check whether this payment method supports recurring payment.
	 *
	 * @return boolean
	 */
	public function isSupportingRecurring(){
		try {
			$adapter = $this->getHelper()->getAuthorizationAdapter(Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
			if ($adapter->isPaymentMethodSupportingRecurring($this->_paymentMethod)) {
				return true;
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
		}
		
		return false;
	}

	/**
	 * Generate and return the form's action url.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return string|boolean
	 */
	public function generateFormActionUrl(Mage_Sales_Model_Order $order){
		$transaction = $this->getHelper()->loadTransactionByOrder($order->getId());
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		
		if ($adapter instanceof Customweb_Payment_Authorization_Server_IAdapter) {
			return $this->getProcessUrl();
		}
		if (method_exists($adapter, 'getFormActionUrl')) {
			return $adapter->getFormActionUrl($transaction->getTransactionObject());
		}
		
		return false;
	}

	/**
	 * Generate and return the hidden form fields.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return string
	 */
	public function generateHiddenFormParameters(Mage_Sales_Model_Order $order){
		$transaction = $this->getHelper()->loadTransactionByOrder($order->getId());
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		
		$hiddenFields = '';
		if (method_exists($adapter, 'getHiddenFormFields')) {
			$hiddenFields = $adapter->getHiddenFormFields($transaction->getTransactionObject());
		}
		
		$jsonObject = array();
		$jsonObject['actionUrl'] = $this->generateFormActionUrl($order);
		$jsonObject['fields'] = $hiddenFields;
		return json_encode($jsonObject);
	}

	/**
	 * Generate and return the visible form fields.
	 *
	 * @param array $parameters
	 * @return string
	 */
	public function generateVisibleFormFields(array $parameters){
		$adapter = $this->getAuthorizationAdapter(false);
		$customerId = $this->_subscription->getCustomer()->getId();
		$paymentCustomerContext = $this->getHelper()->getPaymentCustomerContext($customerId);
		
		$formFields = array();
		$aliasTransaction = null;
		if (!empty($parameters['alias_id']) && $parameters['alias_id'] != 'new') {
			$aliasId = $parameters['alias_id'];
			$alias = $this->loadAlias($aliasId);
			if ($alias->getCustomerId() == $customerId) {
				$aliasTransaction = $this->getHelper()->loadTransactionByOrder($alias->getOrderId())->getTransactionObject();
			}
		}
		
		if (method_exists($adapter, 'getVisibleFormFields')) {
			$formFields = $adapter->getVisibleFormFields($this->getOrderContext(false), $aliasTransaction, null, $paymentCustomerContext);
		}
		
		$paymentCustomerContext->persist();
		
		$result = $this->getFormRenderer()->renderElements($formFields);
		
		return $result;
	}

	/**
	 * Redirect the customer to the payment page.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param array $parameters
	 */
	public function redirectToPaymentPage(Mage_Sales_Model_Order $order, array $parameters){
		$transaction = $this->getHelper()->loadTransactionByOrder($order->getId());
		$adapter = $this->getAuthorizationAdapter(true, $transaction);
		$isHeaderRedirect = $adapter->isHeaderRedirectionSupported($transaction->getTransactionObject(), $parameters);
		if ($isHeaderRedirect) {
			$url = $adapter->getRedirectionUrl($transaction->getTransactionObject(), $parameters);
			$transaction->save();
			header('Location: ' . $url);
			exit();
		}
		else {
			$html = $this->getFormHtml($adapter, $transaction, true, '', $parameters);
			echo $html;
			exit();
		}
	}
}
