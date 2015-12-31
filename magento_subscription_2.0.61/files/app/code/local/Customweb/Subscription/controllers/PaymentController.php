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
class Customweb_Subscription_PaymentController extends Customweb_Subscription_Controller_Abstract {

	/**
	 * Create a payment for the subscription.
	 */
	public function indexAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		if (!$subscription->canPay() || !$subscription->canPayOnline()) {
			Mage::getSingleton('customer/session')->addError(Mage::helper('customweb_subscription')->__('The subscription cannot be authorized.'));
			$this->_redirect('/');
			return;
		}

		$key = $this->getRequest()->getParam('key');
		if (!empty($key)) {
			if ($subscription->getLinkHash() !== $key) {
				Mage::getSingleton('customer/session')->addError(Mage::helper('customweb_subscription')->__('The link is invalid or outdated.'));
				$this->_redirect('/');
				return;
			}
		}
		else {
			$subscriptionCustomer = $subscription->getCustomer();
			if (Mage::getSingleton('customer/session')->getCustomer()->getId() != $subscriptionCustomer->getId()) {
				Mage::getSingleton('customer/session')->addError(Mage::helper('customweb_subscription')->__('You cannot access this subscription.'));
				$this->_redirect('/');
				return;
			}
		}

		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * Redirect customer after a successful payment.
	 */
	public function successAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		Mage::getSingleton('customer/session')->addSuccess(Mage::helper('customweb_subscription')->__('The payment has been accepted.'));

		if (Mage::helper('customer')->isLoggedIn()) {
			$this->_redirect('*/index/view', array(
				'subscription_id' => $subscription->getId()
			));
		}
		else {
			$this->_redirect('/');
		}
	}

	/**
	 * Redirect customer after a failed payment.
	 */
	public function failAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		Mage::getSingleton('customer/session')->addError(Mage::helper('customweb_subscription')->__('The payment has failed.'));

		if (Mage::helper('customer')->isLoggedIn()) {
			$this->_redirect('*/index/view', array(
				'subscription_id' => $subscription->getId()
			));
		}
		else {
			$this->_redirect('/');
		}
	}

	/**
	 * Create a new order for the subscription.
	 */
	public function createOrderAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$this->createOrder($subscription);
	}

	/**
	 * Return the hidden form fields as json string.
	 */
	public function getHiddenFieldsAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$payment = new Customweb_Subscription_Model_Method($this->getSubscription()->getMethodInstance(), $subscription);
		$javaScriptObjectString = $payment->generateHiddenFormParameters($this->getOrder());

		echo $javaScriptObjectString;
	}

	/**
	 * Return the visible form fields as html string.
	 */
	public function getVisibleFieldsAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$payment = new Customweb_Subscription_Model_Method($this->getSubscription()->getMethodInstance(), $subscription);
		echo $payment->generateVisibleFormFields($_REQUEST);
	}

	/**
	 * Return the javascript used for ajax authorization.
	 */
	public function ajaxAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$payment = new Customweb_Subscription_Model_Method($this->getSubscription()->getMethodInstance(), $subscription);
		$javaScriptObjectString = $payment->generateJavascriptForAjax($this->getOrder());

		echo $javaScriptObjectString;
	}

	/**
	 * Redirect the customer to the payment page.
	 */
	public function redirectAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$transaction = $this->createOrder($subscription);

		$payment = new Customweb_Subscription_Model_Method($subscription->getMethodInstance(), $subscription);
		$payment->redirectToPaymentPage($transaction, $_REQUEST);
	}

	/**
	 * Redirect the customer to the iframe page.
	 */
	public function iframeAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$transaction = $this->createOrder($subscription);

		$payment = new Customweb_Subscription_Model_Method($subscription->getMethodInstance(), $subscription);

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->addCss('css/customweb.css');
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
		$iframeBlock = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'iframe',
				array(
					'template' => 'customweb/subscription/payment/iframe.phtml'
				));
		$iframeBlock->setIframeUrl($payment->getIFrameUrl($transaction, $_REQUEST));
		$iframeBlock->setIframeHeight($payment->getIFrameHeight($transaction, $_REQUEST));
		$this->getLayout()->getBlock('content')->append($iframeBlock);
		$this->renderLayout();
	}

	/**
	 * Redirect the customer to the widget page.
	 */
	public function widgetAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		$transaction = $this->createOrder($subscription);

		$payment = new Customweb_Subscription_Model_Method($subscription->getMethodInstance(), $subscription);

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->addCss('css/customweb.css');
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
		$widgetBlock = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'widget',
				array(
					'template' => 'customweb/subscription/payment/widget.phtml'
				));
		$widgetBlock->setWidgetHtml($payment->getWidgetHtml($transaction, $_REQUEST));
		$this->getLayout()->getBlock('content')->append($widgetBlock);
		$this->renderLayout();
	}

	/**
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @throws Exception
	 * @return transaction
	 */
	private function createOrder($subscription){
		if (!$subscription->canPay()) {
			throw new Exception('The subscription cannot be authorized.');
		}

		Mage::register('customweb_subscription_recurring_order', true);
		Mage::getSingleton('core/session')->setSubscriptionId($subscription->getId());
		$order = $subscription->createOrder();
		$order->save();
		$subscription->addOrderRelation($order->getId());
		Mage::unregister('customweb_subscription_recurring_order');
		Mage::getSingleton('core/session')->setSubscriptionOrder($order->getId());

		$method = $order->getPayment()->getMethodInstance();
		return $method->createTransaction($order);
	}
}