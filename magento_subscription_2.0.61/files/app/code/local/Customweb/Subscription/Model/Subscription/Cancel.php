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
 * Contains cancel related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Cancel extends Customweb_Subscription_Model_Subscription_Abstract {

	/**
	 * Email template config paths
	 */
	const XML_PATH_EMAIL_CANCEL_TEMPLATE = 'customweb_subscription/email/template_cancel';
	const XML_PATH_EMAIL_CANCEL_REQUEST_TEMPLATE = 'customweb_subscription/email/template_cancel_request';

	/**
	 * Check if the subscription is canceled.
	 *
	 * @return boolean
	 */
	public function isCanceled(){
		return $this->getStatus() == self::STATUS_CANCELED;
	}

	/**
	 * Check whether the workflow allows to cancel the subscription.
	 *
	 * @return boolean
	 */
	public function canCancel(){
		return $this->checkWorkflow(self::STATUS_CANCELED) && !$this->isCancelRequested();
	}

	/**
	 * Cancel the subscription if allowed.
	 */
	public function cancel(){
		$this->checkWorkflow(self::STATUS_CANCELED, false);
		$this->setStatus(self::STATUS_CANCELED)->save();
		$this->getSubscription()->deletePendingJobs();
		$this->getLogger()->info('The subscription was canceled.');
	}

	/**
	 * Check whether the subscription has been requested to be canceled.
	 *
	 * @return boolean
	 */
	public function isCancelRequested(){
		return $this->getSubscription()->getCancelRequest() == 1;
	}

	/**
	 * Check whether the subscription has a cancelation period.
	 *
	 * @return boolean
	 */
	public function hasCancelPeriod(){
		return $this->getSubscription()->getCancelPeriod() >= 1;
	}

	/**
	 * Request the cancelation of the subscription.
	 * In case of a cancelation
	 * period, it cancel date is set. Otherwise the subscription is canceled
	 * directly.
	 */
	public function requestCancel(){
		$this->checkWorkflow(self::STATUS_CANCELED, false);
		if ($this->hasCancelPeriod()) {
			$this->getSubscription()->setCancelRequest(true);
			$this->save();
			$this->sendCancelRequestEmail();
			$this->getLogger()->info('The cancelation of the subscription was requested.');
		}
		else {
			$this->cancel();
			$this->sendCancelEmail();
		}
	}

	/**
	 * Check whether the cancelation period is over and cancel the subscription.
	 */
	public function processCancelRequest(){
		if ($this->checkWorkflow(self::STATUS_CANCELED) && $this->isCancelRequested()) {
			$this->getSubscription()->setCancelCount($this->getSubscription()->getCancelCount() + 1);
			if ($this->getSubscription()->getCancelCount() == $this->getSubscription()->getCancelPeriod()) {
				$this->cancel();
			}
			else {
				$this->save();
			}
		}
	}

	/**
	 * Send a new cancelation email to the customer.
	 *
	 * @throws Exception
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function sendCancelEmail(){
		if (!$this->isCanceled()) {
			throw new Exception('Subscription has not been canceled.');
		}
		$this->sendEmail(self::XML_PATH_EMAIL_CANCEL_TEMPLATE);
		return $this->getSubscription();
	}

	/**
	 * Send a new cancelation request email to the customer.
	 *
	 * @throws Exception
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function sendCancelRequestEmail(){
		if (!$this->isCancelRequested()) {
			throw new Exception('Cancelation has not been requested.');
		}
		$this->sendEmail(self::XML_PATH_EMAIL_CANCEL_REQUEST_TEMPLATE);
		return $this->getSubscription();
	}

	/**
	 *
	 * @param string $template
	 * @throws Exception
	 */
	private function sendEmail($template){
		$storeId = $this->getSubscription()->getStoreId();

		if ($this->getSubscription()->getCustomerId() != null) {
			$customerName = $this->getSubscription()->getCustomer()->getName();
		}
		else {
			$customerName = $this->getSubscription()->getSubscriberName();
		}

		$template = Mage::getStoreConfig($template, $storeId);
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
		$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

		$this->getSubscription()->sendEmailTemplate($template, $sender,
				array(
					'subscription' => $this->getSubscription(),
					'customer_name' => $customerName,
					'subscription_created_at' => $subscriptionCreatedAt,
					'subscription_period' => $subscriptionPeriod,
					'subscription_end' => $subscriptionEnd,
					'cancel_period' => $this->getSubscription()->getCancelPeriod()
				), $storeId);
	}
}