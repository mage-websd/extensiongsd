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
 * Contains reminder related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Reminder extends Customweb_Subscription_Model_Subscription_Abstract {

	/**
	 *
	 * @param int $storeId
	 * @return Customweb_Subscription_Model_Reminder[]
	 */
	public function getReminders($storeId = null){
		$config = Mage::getStoreConfig('customweb_subscription/email/reminders', $storeId);
		if (empty($config)) {
			return array();
		}
		$reminders = unserialize(base64_decode(Mage::getStoreConfig('customweb_subscription/email/reminders', $storeId)));
		if (!is_array($reminders)) {
			return array();
		}
		return $reminders;
	}

	/**
	 * Send a new reminder email to the customer.
	 *
	 * @param string $template
	 * @throws Exception
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function sendReminderEmail($template){
		if (!$this->getSubscription()->isActive()) {
			throw new Exception('Reminders can only be sent for active subscriptions.');
		}
		
		$storeId = $this->getSubscription()->getStoreId();
		
		if ($this->getSubscription()->getCustomerId() != null) {
			$customerName = $this->getSubscription()->getCustomer()->getName();
		}
		else {
			$customerName = $this->getSubscription()->getSubscriberName();
		}
		
		$sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId);
		
		$appEmulation = Mage::getSingleton('core/app_emulation');
		$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		try {
			$subscriptionCreatedAt = Mage::helper('customweb_subscription')->formatDate($this->getSubscription()->getCreatedAt(), 'medium', true);
			$subscriptionDueDate = Mage::helper('customweb_subscription')->formatDate($this->getSubscription()->getPlan()->getNextDueDate(), 'medium', true);
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
					'subscription_created_at' => $subscriptionCreatedAt,
					'subscription_period' => $subscriptionPeriod,
					'subscription_end' => $subscriptionEnd,
					'cancel_period' => $cancelPeriod,
					'due_date' => $subscriptionDueDate 
				), $storeId);
		
		return $this->getSubscription();
	}
}