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
class Customweb_Subscription_Adminhtml_SubscriptionController extends Mage_Adminhtml_Controller_Action {

	/**
	 * Init layout, menu and breadcrumb
	 *
	 * @return Customweb_Subscription_Adminhtml_SubscriptionController
	 */
	protected function _initAction(){
		$this->loadLayout()->_setActiveMenu('sales/customweb_subscription_subscriptions')->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))->_addBreadcrumb(
				$this->__('Subscriptions'), $this->__('Subscriptions'));
		return $this;
	}

	/**
	 * Subscriptions list.
	 */
	public function indexAction(){
		$this->_title($this->__('Sales'))->_title($this->__('Subscriptions'));

		$this->_initAction()->_addContent($this->getLayout()->createBlock('Customweb_Subscription_Block_Adminhtml_Sales_Subscription_Grid'))->renderLayout();
	}

	/**
	 * Migrate subscriptions to current version.
	 */
	public function migrateAction(){
		Mage::getModel('customweb_subscription/resource_migration')->migrate();
		$this->_getSession()->addSuccess($this->__('All subscriptions are up to date.'));
		$this->_redirect('*/*/index');
	}

	/**
	 * Subscription grid.
	 */
	public function gridAction(){
		$this->loadLayout();
		$this->getResponse()->setBody($this->getLayout()->createBlock('Customweb_Subscription_Block_Adminhtml_Sales_Subscription_Grid')->toHtml());
	}

	/**
	 * View subscription detail.
	 */
	public function viewAction(){
		$this->_title($this->__('Sales'))->_title($this->__('Subscription'));

		if ($subscription = $this->_initSubscription()) {
			$this->_initAction();

			$this->_title(sprintf("#%s", $subscription->getId()));

			$this->renderLayout();
		}
	}

	/**
	 * Generate orders grid for ajax request.
	 */
	public function ordersAction(){
		$this->_initSubscription();
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('customweb_subscription/adminhtml_sales_subscription_view_tab_orders')->toHtml());
	}

	/**
	 * Generate logs grid for ajax request.
	 */
	public function logsAction(){
		$this->_initSubscription();
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('customweb_subscription/adminhtml_sales_subscription_view_tab_logs')->toHtml());
	}

	/**
	 * Generate schedule grid for ajax request.
	 */
	public function scheduleAction(){
		$this->_initSubscription();
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('customweb_subscription/adminhtml_sales_subscription_view_tab_schedule')->toHtml());
	}

	/**
	 * Update subscription's status.
	 */
	public function updateStatusAction(){
		if ($subscription = $this->_initSubscription()) {
			try {
				switch ($this->getRequest()->getParam('action')) {
					case 'cancel':
						$subscription->cancel();
						break;
					case 'suspend':
						$subscription->suspend();
						break;
					case 'activate':
						$subscription->activate();
						break;
				}

				$this->_getSession()->addSuccess(Mage::helper('customweb_subscription')->__('The subscription status has been updated.'));
			}
			catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			catch (Exception $e) {
				$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('Failed to update the subscription.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/view', array(
			'subscription_id' => $subscription->getId()
		));
	}

	/**
	 * Delete the subscription.
	 */
	public function deleteAction(){
		if ($subscription = $this->_initSubscription()) {
			$subscription->delete();
		}
		$this->_redirect('*/*/index');
	}

	/**
	 * Authorize the subscription.
	 */
	public function authorizeAction(){
		if ($subscription = $this->_initSubscription()) {
			if (!$subscription->canRequestPaymentManually()) {
				$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('The subscription cannot be authorized manually because the payment\'s check date is after the next due date.'));
			} else {
				try {
					if (!$subscription->canRequestPaymentManually()) {
						throw new Exception(Mage::helper('customweb_subscription')->__('The subscription cannot be authorized manually.'));
					}
					if (!$subscription->isActive() && !$subscription->canActivate()) {
						throw new Exception(Mage::helper('customweb_subscription')->__('The subscription cannot be activated.'));
					}
					$subscription->scheduleManualPayment();
					$this->_getSession()->addSuccess(Mage::helper('customweb_subscription')->__('The manual payment has been scheduled.'));
				} catch (Exception $e) {
					$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('Failed to manually schedule a payment for the subscription: %s', $e->getMessage()));
				}
			}
		}
		$this->_redirect('*/*/view', array(
			'subscription_id' => $subscription->getId()
		));
	}

	/**
	 * Chooser Source action
	 */
	public function chooserAction(){
		$uniqId = $this->getRequest()->getParam('uniq_id');
		$massAction = $this->getRequest()->getParam('use_massaction', false);
		$productTypeId = $this->getRequest()->getParam('product_type_id', null);

		$productsGrid = $this->getLayout()->createBlock('customweb_subscription/adminhtml_widget_chooser', '',
				array(
					'id' => $uniqId,
					'use_massaction' => $massAction,
					'product_type_id' => $productTypeId,
					'category_id' => $this->getRequest()->getParam('category_id')
				));

		$html = $productsGrid->toHtml();
		$this->getResponse()->setBody($html);
	}

	/**
	 * Change whether the customer is allowed to suspend the subscription.
	 */
	public function changeCanCustomerSuspendAction(){
		if ($subscription = $this->_initSubscription()) {
			$subscription->setCanCustomerSuspend($subscription->canCustomerSuspend() ? 0 : 1)->save();
		}
		$this->_redirect('*/*/view', array(
			'subscription_id' => $subscription->getId()
		));
	}

	/**
	 * Update multiple subscriptions' statuses.
	 */
	public function batchStatusAction(){
		$subscriptionIds = $this->getRequest()->getParam('subscription_ids');
		try {
			if (is_array($subscriptionIds) && !empty($subscriptionIds)) {
				foreach ($subscriptionIds as $subscriptionId) {
					$subscription = Mage::getModel('customweb_subscription/subscription')->load($subscriptionId);
					if ($subscription->getId()) {
						switch ($this->getRequest()->getParam('action')) {
							case 'cancel':
								if ($subscription->canCancel()) {
									$subscription->cancel();
								}
								break;
							case 'suspend':
								if ($subscription->canSuspend()) {
									$subscription->suspend();
								}
								break;
							case 'activate':
								if ($subscription->canActivate()) {
									$subscription->activate();
								}
								break;
						}
					}
				}
			}
			$this->_getSession()->addSuccess($this->__("The subscriptions' statuses have been updated."));
		}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('Failed to update the subscriptions.'));
			Mage::logException($e);
		}
		$this->_redirect('*/*/index');
	}

	/**
	 * Authorize multiple subscriptions.
	 */
	public function batchAuthorizeAction(){
		$subscriptionIds = $this->getRequest()->getParam('subscription_ids');
		try {
			if (is_array($subscriptionIds) && !empty($subscriptionIds)) {
				$scheduled = array();
				foreach ($subscriptionIds as $subscriptionId) {
					$subscription = Mage::getModel('customweb_subscription/subscription')->load($subscriptionId);
					try {
						if (!$subscription->canRequestPaymentManually()) {
							throw new Exception(Mage::helper('customweb_subscription')->__('The subscription cannot be authorized manually.'));
						}
						if (!$subscription->isActive() && !$subscription->canActivate()) {
							throw new Exception(Mage::helper('customweb_subscription')->__('The subscription cannot be activated.'));
						}
						$subscription->scheduleManualPayment();
						$scheduled[] = $subscription->getReferenceId();
					} catch (Exception $e) {
						$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('Subscription # %s failed: %s', $subscription->getReferenceId(), $e->getMessage()));
					}
				}
			}
			$this->_getSession()->addSuccess($this->__('Manual payments have been scheduled for the subscriptions # %s.', implode(', ', $scheduled)));
		}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('Failed to schedule manual payments.'));
			Mage::logException($e);
		}
		$this->_redirect('*/*/index');
	}

	/**
	 * Delete multiple subscriptions.
	 */
	public function batchDeleteAction(){
		$subscriptionIds = $this->getRequest()->getParam('subscription_ids');
		try {
			if (is_array($subscriptionIds) && !empty($subscriptionIds)) {
				foreach ($subscriptionIds as $subscriptionId) {
					$subscription = Mage::getModel('customweb_subscription/subscription')->load($subscriptionId);
					$subscription->delete();
				}
			}
			$this->_getSession()->addSuccess($this->__('The subscriptions have been deleted.'));
		}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addError(Mage::helper('customweb_subscription')->__('Failed to delete the subscriptions.'));
			Mage::logException($e);
		}
		$this->_redirect('*/*/index');
	}

	/**
	 * Initialize subscription model instance
	 *
	 * @return Customweb_Subscription_Model_Subscription|boolean
	 */
	private function _initSubscription(){
		$id = $this->getRequest()->getParam('subscription_id');
		$subscription = Mage::getModel('customweb_subscription/subscription')->load($id);

		if (!$subscription->getId()) {
			$this->_getSession()->addError($this->__('This subscription no longer exists.'));
			$this->_redirect('*/*/');
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
			return false;
		}
		Mage::register('current_subscription', $subscription);
		return $subscription;
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('sales/customweb_subscription');
	}
}