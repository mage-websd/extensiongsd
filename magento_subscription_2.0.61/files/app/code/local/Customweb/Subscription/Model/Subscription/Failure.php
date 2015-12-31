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
 * Contains failure related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Failure extends Customweb_Subscription_Model_Subscription_Abstract {
	
	/**
	 * Email template config paths
	 */
	const XML_PATH_EMAIL_FAILURE_TEMPLATE = 'customweb_subscription/email/template_failure';

	/**
	 * Check if the subscription is failed.
	 *
	 * @return boolean
	 */
	public function isFailed(){
		return $this->getStatus() == self::STATUS_FAILED;
	}

	/**
	 * Mark the subscription as failed.
	 */
	public function markAsFailed(){
		$this->setStatus(self::STATUS_FAILED)->save();
		$this->getSubscription()->deletePendingJobs();
		$this->sendFailureEmail();
	}

	/**
	 * Send a new failure email to the customer.
	 *
	 * @throws Exception
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function sendFailureEmail(){
		if (!$this->isFailed()) {
			throw new Exception('Payment has not failed.');
		}
		
		$storeId = $this->getSubscription()->getStoreId();
		
		if ($this->getSubscription()->getCustomerId() != null) {
			$customerName = $this->getSubscription()->getCustomer()->getName();
		}
		else {
			$customerName = $this->getSubscription()->getSubscriberName();
		}
		
		$template = Mage::getStoreConfig(self::XML_PATH_EMAIL_FAILURE_TEMPLATE, $storeId);
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
					'subscription_created_at' => $subscriptionCreatedAt,
					'subscription_period' => $subscriptionPeriod,
					'subscription_end' => $subscriptionEnd,
					'cancel_period' => $cancelPeriod 
				), $storeId);
		
		return $this->getSubscription();
	}
}