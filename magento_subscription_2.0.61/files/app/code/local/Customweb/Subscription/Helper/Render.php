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
class Customweb_Subscription_Helper_Render extends Mage_Core_Helper_Abstract {

	/**
	 * Render label for specified status.
	 *
	 * @param string $status
	 */
	public function getStatusLabel($status){
		switch ($status) {
			case Customweb_Subscription_Model_Subscription::STATUS_UNKNOWN:
				return $this->__('Not initialized');
			case Customweb_Subscription_Model_Subscription::STATUS_ACTIVE:
				return $this->__('Active');
			case Customweb_Subscription_Model_Subscription::STATUS_PENDING:
				return $this->__('Pending');
			case Customweb_Subscription_Model_Subscription::STATUS_SUSPENDED:
				return $this->__('Suspended');
			case Customweb_Subscription_Model_Subscription::STATUS_CANCELED:
				return $this->__('Canceled');
			case Customweb_Subscription_Model_Subscription::STATUS_FAILED:
				return $this->__('Failed');
			case Customweb_Subscription_Model_Subscription::STATUS_ERROR:
				return $this->__('Error');
			case Customweb_Subscription_Model_Subscription::STATUS_EXPIRED:
				return $this->__('Expired');
			case Customweb_Subscription_Model_Subscription::STATUS_AUTHORIZED:
				return $this->__('Authorized');
			case Customweb_Subscription_Model_Subscription::STATUS_PAID:
				return $this->__('Paid');
		}
		return $status;
	}

	/**
	 * Render a plan to a readable string.
	 *
	 * @param Customweb_Subscription_Model_Plan $plan
	 * @return string
	 */
	public function renderPlan(Customweb_Subscription_Model_Plan $plan){
		if ($plan == null) {
			throw new Exception("The given plan object is NULL.");
		}
		return $this->__('%s %s cycle', $plan->getPeriodFrequency(), $plan->getPeriodUnit()->getLabel());
	}

	/**
	 * Render a plan end to a readable string.
	 *
	 * @param Customweb_Subscription_Model_Plan $plan
	 * @return string
	 */
	public function renderPlanEnd(Customweb_Subscription_Model_Plan $plan){
		$maxCycles = $plan->getPeriodMaxCycles();
		if (empty($maxCycles)) {
			return $this->__('Repeats until canceled.');
		}
		else {
			return $this->__('Repeats %s time(s).', $maxCycles);
		}
	}

	/**
	 * Render the subscription's next due date.
	 *
	 * @param Customweb_Subscription_Model_Plan $plan
	 * @return string
	 */
	public function renderNextDueDate(Customweb_Subscription_Model_Plan $plan){
		if ($plan->getSubscription() == null) {
			return;
		}
		try {
			$nextDueDate = $plan->getNextDueDate();
		}
		catch (Exception $e) {
			$nextDueDate = null;
		}
		if ($nextDueDate == null || $plan->getSubscription()->isExpired()) {
			return $this->__('Expired');
		}
		if ($plan->getSubscription()->isCanceled()) {
			return $this->__('Canceled');
		}
		if ($plan->getSubscription()->isSuspended()) {
			return $this->__('Suspended');
		}
		if ($plan->getSubscription()->isFailed()) {
			return $this->__('Failed');
		}
		if ($plan->getSubscription()->isError()) {
			return $this->__('Error');
		}
		return Mage::helper('customweb_subscription')->formatDate($nextDueDate, 'medium', true);
	}
}
