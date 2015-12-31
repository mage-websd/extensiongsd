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
 * Wrapper for transaction context to override some values needed
 * to enable recurring payments.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_TransactionContext implements Customweb_Payment_Authorization_ITransactionContext,
		Customweb_Payment_Authorization_PaymentPage_ITransactionContext, Customweb_Payment_Authorization_Hidden_ITransactionContext,
		Customweb_Payment_Authorization_Iframe_ITransactionContext, Customweb_Payment_Authorization_Server_ITransactionContext,
		Customweb_Payment_Authorization_Moto_ITransactionContext, Customweb_Payment_Authorization_Ajax_ITransactionContext,
		Customweb_Payment_Authorization_Widget_ITransactionContext, Customweb_Payment_Authorization_Recurring_ITransactionContext {

	/**
	 * The original transaction context.
	 *
	 * @var Customweb_Payment_Authorization_ITransactionContext
	 */
	protected $_transactionContext = null;

	/**
	 * Is the transaction the initial or a recurring one?
	 *
	 * @var boolean
	 */
	protected $_isInitialTransaction = false;

	/**
	 * The subscription model.
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	protected $_subscription = null;

	/**
	 *
	 * @param Customweb_Payment_Authorization_ITransactionContext $transactionContext
	 * @param boolean $initial
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 */
	public function __construct(Customweb_Payment_Authorization_ITransactionContext $transactionContext, $isInitialTransaction = false, Customweb_Subscription_Model_Subscription $subscription = null){
		$this->_transactionContext = $transactionContext;
		$this->_isInitialTransaction = $isInitialTransaction;
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
			$this->_transactionContext,
			$name
		), $arguments);
	}

	public function getNotificationUrl(){
		return $this->_transactionContext->getNotificationUrl();
	}

	public function getSuccessUrl(){
		return $this->_transactionContext->getSuccessUrl();
	}

	public function getFailedUrl(){
		return $this->_transactionContext->getFailedUrl();
	}

	public function getBackendSuccessUrl(){
		return $this->_transactionContext->getBackendSuccessUrl();
	}

	public function getBackendFailedUrl(){
		return $this->_transactionContext->getBackendFailedUrl();
	}

	public function getOrderContext(){
		return $this->_transactionContext->getOrderContext();
	}

	public function getTransactionId(){
		return $this->_transactionContext->getTransactionId();
	}

	public function getOrderId(){
		return $this->_transactionContext->getOrderId();
	}

	public function isOrderIdUnique(){
		return $this->_transactionContext->isOrderIdUnique();
	}

	public function getCapturingMode(){
		return $this->_transactionContext->getCapturingMode();
	}

	public function getAlias(){
		return $this->_transactionContext->getAlias();
	}

	/**
	 * If it is the initial transaction, create an alias.
	 */
	public function createRecurringAlias(){
		return $this->_isInitialTransaction;
	}

	/**
	 * Add the subscription id to the custom parameters.
	 */
	public function getCustomParameters(){
		$params = array();
		if ($this->_subscription != null) {
			$params['cwsubscriptionid'] = $this->_subscription->getId();
		}
		return array_merge($this->_transactionContext->getCustomParameters(), $params);
	}

	public function getPaymentCustomerContext(){
		return $this->_transactionContext->getPaymentCustomerContext();
	}

	public function getIframeBreakOutUrl(){
		return $this->_transactionContext->getIframeBreakOutUrl();
	}

	public function getJavaScriptSuccessCallbackFunction(){
		return $this->_transactionContext->getJavaScriptSuccessCallbackFunction();
	}

	public function getJavaScriptFailedCallbackFunction(){
		return $this->_transactionContext->getJavaScriptFailedCallbackFunction();
	}

	/**
	 * If it is a recurring transaction, return the corresponding initial transaction.
	 */
	public function getInitialTransaction(){
		if ($this->_subscription != null) {
			return $this->_subscription->getPaymentTransaction()->getTransactionObject();
		}
	}
}
