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
 * Contains schedule related methods.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Subscription_Schedule extends Customweb_Subscription_Model_Subscription_Abstract {

	private $_manual = false;
	private $_preventScheduling = false;

	/**
	 * Action constants
	 */
	const ACTION_PAY = 'pay';
	const ACTION_CHECK = 'check';
	const ACTION_REMIND = 'remind';

	const ACTION_PAY_MANUAL = 'pay_manual';
	const ACTION_CHECK_MANUAL = 'check_manual';

	/**
	 * Delete this subscription's pending scheduled jobs.
	 */
	public function deletePendingJobs(){
		Mage::getModel('customweb_subscription/schedule')->getResource()->deleteBySubscription($this->getSubscription()->getId());
	}

	/**
	 * Schedule the next jobs to send reminder email, request and validate a new payment.
	 */
	public function scheduleNextJobs(){
		if ($this->_preventScheduling) return;

		$scheduler = Mage::getModel('customweb_subscription/scheduler');
		try {
			$dueDate = $this->getSubscription()->getPlan()->getNextDueDate();
		}
		catch (Exception $e) {
			$dueDate = null;
		}
		if ($dueDate !== null) {
			$scheduler->createJob($this->getSubscription(), $dueDate, 'pay');
			$scheduler->createJob($this->getSubscription(), $this->getCheckDate($dueDate), 'check', Mage::helper('customweb_subscription')->toDateString($dueDate));

			foreach ($this->getSubscription()->getReminders() as $reminder) {
				$scheduler->createJob($this->getSubscription(), $reminder->getRemindDate($dueDate), 'remind', $reminder->getEmailTemplate());
			}
		}
	}

	/**
	 * Process a job.
	 *
	 * @param string $action
	 * @param string $additionalData
	 */
	public function processJob($action, $additionalData = null){
		switch ($action) {
			case self::ACTION_PAY:
				$this->pay();
				break;
			case self::ACTION_CHECK:
				$this->check($additionalData);
				break;
			case self::ACTION_REMIND:
				$this->remind($additionalData);
				break;
			case self::ACTION_PAY_MANUAL:
				$this->_manual = true;
				$this->pay();
				$this->_manual = false;
				break;
			case self::ACTION_CHECK_MANUAL:
				$this->_manual = true;
				$this->check($additionalData);
				$this->_manual = false;
				break;
		}
	}

	/**
	 * Calculate the checkdate based on the given due date.
	 *
	 * @param Zend_Date $dueDate
	 */
	public function getCheckDate(Zend_Date $dueDate){
		$paytimeConfig = Mage::helper('customweb_subscription')->getPaytime();
		$checkDate = new Zend_Date($dueDate);
		$checkDate->add($paytimeConfig['count'], Customweb_Subscription_Model_PeriodUnit::valueOf($paytimeConfig['unit'])->getDateConstant());
		return $checkDate;
	}

	private function pay(){
		try {
			if ($this->_manual) {
				if ($this->getSubscription()->canActivate()) {
					$this->_preventScheduling = true;
					$this->getSubscription()->activate();
					$this->_preventScheduling = false;
				}
				if (!$this->getSubscription()->isActive()) {
					throw new Exception(Mage::helper('customweb_subscription')->__('The subscription cannot be activated.'));
				}
			}

			if ($this->getSubscription()->isSuspended() || $this->getSubscription()->isFailed() || $this->getSubscription()->isExpired() ||
					$this->getSubscription()->isCanceled()) {
				return;
			}

			$this->getSubscription()->requestPayment();
			$this->getLogger()->info($this->_manual ? 'Manually requested a new payment.' : 'Requested a new payment.');
		}
		catch (Customweb_Payment_Exception_RecurringPaymentErrorException $e) {
			$this->getLogger()->error('The payment failed: ' . $e->getMessage());
			if ($this->_manual || Mage::registry('customweb_subscription_schedule')->getCount() >= Customweb_Subscription_Model_Scheduler::RETRIES) {
				$this->getSubscription()->markAsFailed();
			}
			else {
				throw $e;
			}
		}
		catch (Exception $e) {
			$this->getLogger()->error('An error occurred when requesting a new payment: ' . $e->getMessage());
			if ($this->_manual || Mage::registry('customweb_subscription_schedule')->getCount() >= Customweb_Subscription_Model_Scheduler::RETRIES) {
				$this->getSubscription()->markAsError();
			}
			else {
				throw $e;
			}
		}
	}

	private function check($dueDate = null){
		if ($this->getSubscription()->isSuspended() || $this->getSubscription()->isFailed() || $this->getSubscription()->isExpired() ||
				 $this->getSubscription()->isCanceled()) {
			return;
		}

		try {
			$this->getSubscription()->validatePayment($dueDate, $this->_manual);
			$this->getSubscription()->processCancelRequest();
		}
		catch (Exception $e) {
			$this->getLogger()->error('An error occurred when checking the payment: ' . $e->getMessage());
			$this->getSubscription()->markAsError();
		}
	}

	private function remind($emailTemplate){
		if (!$this->getSubscription()->isActive()) {
			return;
		}

		$this->getSubscription()->sendReminderEmail($emailTemplate);

		$this->getLogger()->info('A reminder email with template %s was sent.', array($emailTemplate));
	}
}