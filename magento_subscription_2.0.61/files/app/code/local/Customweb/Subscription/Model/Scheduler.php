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
class Customweb_Subscription_Model_Scheduler {

	/**
	 * Scheduler constants
	 */
	const RETRIES = 3;
	const TIMEOUT_IN_MINUTES = 5;

	/**
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @param Zend_Date $scheduledAt
	 * @param string $action
	 * @param string $additionalData
	 */
	public function createJob(Customweb_Subscription_Model_Subscription $subscription, Zend_Date $scheduledAt, $action, $additionalData = null){
		$schedule = Mage::getModel('customweb_subscription/schedule');
		$schedule->setSubscriptionId($subscription->getId());
		$schedule->setStatus(Customweb_Subscription_Model_Schedule::STATUS_PENDING);
		$schedule->setCreatedAt(Mage::helper('customweb_subscription')->toDateString(Zend_Date::now()));
		$schedule->setScheduledAt(Mage::helper('customweb_subscription')->toDateString($scheduledAt));
		$schedule->setCount(0);
		$schedule->setAction($action);
		$schedule->setAdditionalData($additionalData);
		$schedule->save();
	}

	/**
	 * Process due jobs.
	 */
	public function processJobs(){
		$maxExecutionEndTime = Customweb_Core_Util_System::getScriptExecutionEndTime();

		$this->resetTimeoutJobs();

		$schedules = $this->getPendingSchedules();
		foreach ($schedules->getIterator() as $schedule) {
			/**
			 *
			 * @var $schedule Customweb_Subscription_Model_Schedule
			 */
			if ($this->isScheduleDue($schedule)) {
				// If we have only 10 seconds left until the server will kill the script, we return to give
				// the rest of the code the time to finish.
				if ($maxExecutionEndTime - time() < 10) {
					return;
				}

				try {
					if (!$schedule->tryLockJob()) {
						return;
					}
					$schedule->setExecutedAt(Mage::helper('customweb_subscription')->toDateString(Zend_Date::now()))->save();

					$this->processJob($schedule);

					$schedule->setStatus(Customweb_Subscription_Model_Schedule::STATUS_SUCCESS)->setFinishedAt(
							Mage::helper('customweb_subscription')->toDateString(Zend_Date::now()))->save();
				}
				catch (Exception $e) {
					Mage::logException($e);
					$schedule->setMessages($e->__toString());
					$this->reschedule($schedule);
				}
				$schedule->save();
			}
		}
		$this->cleanUp();
	}

	/**
	 * Delete done jobs and log entries.
	 */
	private function cleanUp(){
		$date = Zend_Date::now();
		$date->sub(1, Zend_Date::WEEK);
		Mage::getModel('customweb_subscription/schedule')->getResource()->deleteByStatusAndDate(Customweb_Subscription_Model_Schedule::STATUS_SUCCESS,
				$date);

		Mage::helper('customweb_subscription/log')->cleanUp();
	}

	/**
	 * Process a job.
	 *
	 * @param Customweb_Subscription_Model_Schedule $schedule
	 */
	private function processJob(Customweb_Subscription_Model_Schedule $schedule){
		if ($schedule->getSubscription() == null || $schedule->getSubscription()->getId() == 0) {
			return;
		}
		Mage::unregister('customweb_subscription_schedule');
		Mage::register('customweb_subscription_schedule', $schedule);
		try {
			$schedule->getSubscription()->processJob($schedule->getAction(), $schedule->getAdditionalData());
		}
		catch (Exception $e) {
			Mage::unregister('customweb_subscription_schedule');
			throw $e;
		}
	}

	/**
	 * Get all pending schedules ordered as they should be executed.
	 *
	 * @return Customweb_Subscription_Model_Resource_Schedule_Collection
	 */
	private function getPendingSchedules(){
		return Mage::getModel('customweb_subscription/schedule')->getCollection()->addFieldToFilter('status',
				Customweb_Subscription_Model_Schedule::STATUS_PENDING)->addOrder('scheduled_at', 'ASC')->load();
	}

	/**
	 * Check if the job is due to be executed.
	 *
	 * @param Customweb_Subscription_Model_Schedule $schedule
	 * @return boolean
	 */
	private function isScheduleDue(Customweb_Subscription_Model_Schedule $schedule){
		$now = time();
		$time = strtotime($schedule->getScheduledAt());
		return $time <= $now;
	}

	/**
	 * Reset the jobs that failed during execution and are still in status running.
	 */
	private function resetTimeoutJobs(){
		$date = Zend_Date::now();
		$date->sub(self::TIMEOUT_IN_MINUTES, Zend_Date::MINUTE);
		$schedules = Mage::getModel('customweb_subscription/schedule')->getCollection()->addFieldToFilter('status',
				Customweb_Subscription_Model_Schedule::STATUS_RUNNING)->addFieldToFilter('executed_at',
				array(
					'to' => Mage::helper('customweb_subscription')->toDateString($date)
				))->load();
		foreach ($schedules as $schedule) {
			$this->reschedule($schedule);
			$schedule->save();
		}
	}

	/**
	 * Reschedule a job after an exception was thrown.
	 *
	 * The job is rescheduled some times (as defined) and then, if still not successful, set to status error.
	 *
	 * @param Customweb_Subscription_Model_Schedule $schedule
	 */
	private function reschedule(Customweb_Subscription_Model_Schedule $schedule){
		if ($schedule->getCount() < self::RETRIES) {
			$schedule->setStatus(Customweb_Subscription_Model_Schedule::STATUS_PENDING)->setCount($schedule->getCount() + 1);
		}
		else {
			$schedule->setStatus(Customweb_Subscription_Model_Schedule::STATUS_ERROR);
		}
	}
}