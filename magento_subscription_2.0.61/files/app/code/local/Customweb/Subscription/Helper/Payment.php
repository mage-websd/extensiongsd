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
class Customweb_Subscription_Helper_Payment extends Mage_Core_Helper_Abstract {
	
	/**
	 * All payment methods
	 *
	 * @var array
	 */
	protected $_paymentMethods = array();
	
	/**
	 * Active payment methods
	 *
	 * @var array
	 */
	protected $_activePaymentMethods = array();

	/**
	 * Return all payment methods.
	 *
	 * @param string $store
	 * @return array
	 */
	public function getPaymentMethods($store = null){
		$key = $store;
		if ($store == null) {
			$key = 'null';
		}
		if (!isset($this->_paymentMethods[$key])) {
			$methods = array();
			foreach (Mage::getSingleton('payment/config')->getAllMethods() as $paymentCode => $paymentModel) {
				if (isset($paymentModel) && $this->canPaymentMethodManageSubscriptions($paymentModel)) {
					$methods[$paymentCode] = $paymentModel;
				}
			}
			$this->_paymentMethods[$key] = $methods;
		}
		
		return $this->_paymentMethods[$key];
	}

	/**
	 * Get the codes of all payment methods codes that can be used to buy
	 * recurring products.
	 *
	 * @param string $store
	 * @return array
	 */
	public function getPaymentMethodsCodes($store = null){
		return explode(',', Mage::getStoreConfig('customweb_subscription/general/payment_methods', $store));
	}

	/**
	 * Return all active payment methods.
	 *
	 * @param string $store
	 */
	public function getActivePaymentMethods($store = null){
		$key = $store;
		if ($store == null) {
			$key = 'null';
		}
		if (!isset($this->_activePaymentMethods[$key])) {
			$methods = $this->getPaymentMethodsCodes($store);
			if (is_array($methods)) {
				$paymentMethods = Mage::helper('payment')->getPaymentMethods($store);
				$result = array();
				foreach ($methods as $code) {
					$method = Mage::helper('payment')->getMethodInstance($code);
					if ($method) {
						$result[] = $method;
					}
				}
				$this->_activePaymentMethods[$key] = $result;
			}
			else {
				$this->_activePaymentMethods[$key] = array();
			}
		}
		return $this->_activePaymentMethods[$key];
	}

	/**
	 * Check whether the payment method is enabled to manage subscriptions.
	 *
	 * @param Mage_Payment_Model_Method_Abstract $paymentMethod
	 * @param string $store
	 * @return boolean
	 */
	public function isPaymentMethodEnabled(Mage_Payment_Model_Method_Abstract $paymentMethod, $store = null){
		return in_array($paymentMethod->getCode(), $this->getPaymentMethodsCodes($store)) && $this->canPaymentMethodManageSubscriptions(
				$paymentMethod);
	}

	/**
	 * Check whether the payment method can manage subscriptions.
	 *
	 * @param Mage_Payment_Model_Method_Abstract $paymentMethod
	 * @return boolean
	 */
	public function canPaymentMethodManageSubscriptions(Mage_Payment_Model_Method_Abstract $paymentMethod){
		return $paymentMethod instanceof Customweb_Payment_Authorization_IPaymentMethod ||
				 $paymentMethod instanceof Customweb_Subscription_Model_Payment_Abstract || in_array($paymentMethod->getCode(), 
						array(
							'IsrInvoice' 
						));
	}
}