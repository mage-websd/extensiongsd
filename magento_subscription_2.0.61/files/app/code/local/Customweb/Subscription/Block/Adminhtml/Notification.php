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
class Customweb_Subscription_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Template {
	protected $_failedSubscriptions = null;
	protected $_errorSubscriptions = null;
	protected $_scheduleErrors = null;
	protected $_unmigratedSubscriptions = null;

	public function isAdminNotificationEnabled(){
		if (!$this->isOutputEnabled('Mage_AdminNotification')) {
			return false;
		}
		return true;
	}

	public function isSubscriptionController(){
		if ($this->getRequest()->getControllerName() == 'subscription') {
			return false;
		}
		return true;
	}

	public function getFailedSubscriptions(){
		if ($this->_failedSubscriptions == null) {
			$this->_failedSubscriptions = Mage::getModel('customweb_subscription/subscription')->getCollection()->addFieldToFilter('status',
					Customweb_Subscription_Model_Subscription::STATUS_FAILED)->count();
		}
		return $this->_failedSubscriptions;
	}

	public function getErrorSubscriptions(){
		if ($this->_errorSubscriptions == null) {
			$this->_errorSubscriptions = Mage::getModel('customweb_subscription/subscription')->getCollection()->addFieldToFilter('status',
					Customweb_Subscription_Model_Subscription::STATUS_ERROR)->count();
		}
		return $this->_errorSubscriptions;
	}

	public function getFailedSubscriptionsUrl(){
		return $this->getUrl('adminhtml/subscription/index', array(
			'status_filter' => 'failed'
		));
	}

	public function getErrorSubscriptionsUrl(){
		return $this->getUrl('adminhtml/subscription/index', array(
			'status_filter' => 'error'
		));
	}

	public function hasScheduleErrors(){
		if ($this->_scheduleErrors == null) {
			$this->_scheduleErrors = count(
					Mage::getModel('customweb_subscription/schedule')->getCollection()->addFieldToFilter('status',
							Customweb_Subscription_Model_Schedule::STATUS_ERROR)->getAllIds()) != 0;
		}
		return $this->_scheduleErrors;
	}

	public function hasUnmigratedSubscriptions(){
		if ($this->_unmigratedSubscriptions == null) {
			$this->_unmigratedSubscriptions = count(
					Mage::getModel('customweb_subscription/subscription')->getCollection()->addFieldToFilter('version',
							array(
								'neq' => Customweb_Subscription_Model_Resource_Migration::CURRENT_VERSION
							))) != 0;
		}
		return $this->_unmigratedSubscriptions;
	}

	public function getMigrationUrl(){
		return $this->getUrl('adminhtml/subscription/migrate');
	}
}
