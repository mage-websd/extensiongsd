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
class Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct(){
		$this->_objectId = 'subscription_id';
		$this->_blockGroup = 'customweb_subscription';
		$this->_controller = 'adminhtml_sales_subscription';
		$this->_mode = 'view';

		parent::__construct();

		$this->_removeButton('delete');
		$this->_removeButton('reset');
		$this->_removeButton('save');
		$this->setId('customweb_subscription_view');
		$subscription = $this->getSubscription();

		$message = Mage::helper('customweb_subscription')->__('Are you sure you want to permanently delete this subscription?');
		$this->_addButton('subscription_delete',
				array(
					'label' => Mage::helper('customweb_subscription')->__('Delete'),
					'class' => 'delete',
					'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getDeleteUrl() . '\')'
				));

		if ($subscription->canCancel()) {
			$message = Mage::helper('customweb_subscription')->__('Are you sure you want to cancel this subscription?');
			$this->_addButton('subscription_cancel',
					array(
						'label' => Mage::helper('customweb_subscription')->__('Cancel'),
						'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getCancelUrl() . '\')'
					));
		}

		if ($subscription->canSuspend()) {
			$this->_addButton('subscription_suspend',
					array(
						'label' => Mage::helper('customweb_subscription')->__('Suspend'),
						'onclick' => 'setLocation(\'' . $this->getSuspendUrl() . '\')'
					));
		}

		if ($subscription->canActivate()) {
			$this->_addButton('subscription_activate',
					array(
						'label' => Mage::helper('customweb_subscription')->__('Activate'),
						'onclick' => 'setLocation(\'' . $this->getActivateUrl() . '\')'
					));

			if ($subscription->canRequestPaymentManually()) {
				$message = Mage::helper('customweb_subscription')->__('Are you sure you want to manually request a payment?');
				$this->_addButton('subscription_authorize',
						array(
							'label' => Mage::helper('customweb_subscription')->__('Activate & Request Payment'),
							'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getAuthorizeUrl() . '\')'
						));
			}
		}

		if ($subscription->canAuthorize() && $subscription->canRequestPaymentManually()) {
			$message = Mage::helper('customweb_subscription')->__('Are you sure you want to manually request a payment?');
			$this->_addButton('subscription_authorize',
					array(
						'label' => Mage::helper('customweb_subscription')->__('Request Payment'),
						'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getAuthorizeUrl() . '\')'
					));
		}
	}

	/**
	 * Retrieve subscription model object
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		return Mage::registry('current_subscription');
	}

	/**
	 * Retrieve Order Identifier
	 *
	 * @return int
	 */
	public function getSubscriptionId(){
		return $this->getSubscription()->getId();
	}

	public function getHeaderText(){
		return Mage::helper('customweb_subscription')->__('Subscription # %s | %s', $this->getSubscription()->getReferenceId(),
			Mage::helper('customweb_subscription')->formatDate($this->getSubscription()->getCreatedAt(), 'medium', true));
	}

	public function getUrl($params = '', $params2 = array()){
		$params2['subscription_id'] = $this->getSubscriptionId();
		return parent::getUrl($params, $params2);
	}

	public function getDeleteUrl(){
		return $this->getUrl('*/*/delete', array(
			'subscription_id' => $this->getSubscriptionId()
		));
	}

	public function getCancelUrl(){
		return $this->getUrl('*/*/updateStatus', array(
			'subscription_id' => $this->getSubscriptionId(),
			'action' => 'cancel'
		));
	}

	public function getSuspendUrl(){
		return $this->getUrl('*/*/updateStatus', array(
			'subscription_id' => $this->getSubscriptionId(),
			'action' => 'suspend'
		));
	}

	public function getActivateUrl(){
		return $this->getUrl('*/*/updateStatus', array(
			'subscription_id' => $this->getSubscriptionId(),
			'action' => 'activate'
		));
	}

	public function getAuthorizeUrl(){
		return $this->getUrl('*/*/authorize', array(
			'subscription_id' => $this->getSubscriptionId()
		));
	}

	/**
	 * Return back url for view grid
	 *
	 * @return string
	 */
	public function getBackUrl(){
		return $this->getUrl('*/*/');
	}
}