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
class Customweb_Subscription_IndexController extends Customweb_Subscription_Controller_Abstract {

	/**
	 *
	 * @var Mage_Customer_Model_Session
	 */
	private $_session = null;

	/**
	 * Limit access to protected actions.
	 */
	public function preDispatch(){
		parent::preDispatch();
		if (!$this->getRequest()->isDispatched()) {
			return;
		}

		$action = $this->getRequest()->getActionName();
		$openActions = array(
			'applyPlan'
		);
		$pattern = '/^(' . implode('|', $openActions) . ')/i';

		$this->_session = Mage::getSingleton('customer/session');
		if (!preg_match($pattern, $action)) {
			if (!$this->_session->authenticate($this)) {
				$this->setFlag('', 'no-dispatch', true);
			}
			Mage::register('current_customer', $this->_session->getCustomer());
		}
		else {
			$this->_session->setNoReferer(true);
		}
	}

	/**
	 * Subscription list.
	 */
	public function indexAction(){
		$this->_title($this->__('Subscriptions'));
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
	}

	/**
	 * Subscription view.
	 */
	public function viewAction(){
		$this->_viewAction();
	}

	/**
	 * Related orders view.
	 */
	public function ordersAction(){
		$this->_viewAction();
	}

	/**
	 * Request the cancelation of the subscription.
	 */
	public function cancelAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		if ($subscription->canCancel()) {
			$subscription->requestCancel();

			if ($subscription->hasCancelPeriod()) {
				$this->_session->addSuccess(Mage::helper('customweb_subscription')->__('The cancelation request has been received.'));
			}
			else {
				$this->_session->addSuccess(Mage::helper('customweb_subscription')->__('The subscription has been canceled.'));
			}
		}

		$this->_redirect('*/*/view', array(
			'subscription_id' => $subscription->getId()
		));
	}

	/**
	 * Suspend the subscription.
	 */
	public function suspendAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		if ($subscription->canSuspend() && $subscription->canCustomerSuspend()) {
			$subscription->suspend();
		}

		$this->_redirect('*/*/view', array(
			'subscription_id' => $subscription->getId()
		));
	}

	/**
	 * Activate the subscription.
	 */
	public function activateAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		$subscription = Mage::registry('current_subscription');

		if ($subscription->isSuspended() && $subscription->canActivate()) {
			$subscription->activate();
		}

		$this->_redirect('*/*/view', array(
			'subscription_id' => $subscription->getId()
		));
	}

	/**
	 * Apply subscription plan to cart
	 */
	public function applyPlanAction(){
		$quote = Mage::getSingleton('checkout/cart')->getQuote();

		/**
		 * No reason continue with empty shopping cart
		 */
		if (!$quote->getItemsCount()) {
			$this->_redirect('checkout/cart');
			return;
		}

		$index = $this->getRequest()->getParam('subscription_plan');
		if ($index != '') {
			$subscriptionPlan = Mage::getModel('customweb_subscription/cartPlan')->loadByIndex($index);
		}
		else {
			$subscriptionPlan = null;
		}

		$oldSubscriptionPlan = $quote->getSubscriptionPlan();

		if ($subscriptionPlan == null && $oldSubscriptionPlan == null) {
			$this->_redirect('checkout/cart');
			return;
		}

		try {
			$quote->setSubscriptionPlan($subscriptionPlan)->collectTotals()->save();

			if ($subscriptionPlan != null) {
				Mage::getSingleton('checkout/session')->addSuccess(
						$this->__('You subscribed to the plan <em>%s</em>.', Mage::helper('core')->htmlEscape($subscriptionPlan->getDescription())));
			}
			else {
				Mage::getSingleton('checkout/session')->addSuccess($this->__('The subscription plan was removed.'));
			}
		}
		catch (Mage_Core_Exception $e) {
			Mage::getSingleton('checkout/session')->addError($e->getMessage());
		}
		catch (Exception $e) {
			Mage::getSingleton('checkout/session')->addError($this->__('Cannot apply the subscription plan.'));
			Mage::logException($e);
		}

		$this->_redirect('checkout/cart');
	}

	private function _viewAction(){
		if (!$this->_loadValidSubscription()) {
			return;
		}
		try {
			$subscription = Mage::registry('current_subscription');
			$this->_title(Mage::helper('customweb_subscription')->__('Subscriptions'))->_title(
					Mage::helper('customweb_subscription')->__('Subscription #%s', $subscription->getReferenceId()));
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
			if ($navigationBlock) {
				$navigationBlock->setActive('customweb_subscription/subscription/');
			}
			$this->renderLayout();
			return;
		}
		catch (Mage_Core_Exception $e) {
			$this->_session->addError($e->getMessage());
		}
		catch (Exception $e) {
			Mage::logException($e);
		}
		$this->_redirect('*/*/');
	}
}