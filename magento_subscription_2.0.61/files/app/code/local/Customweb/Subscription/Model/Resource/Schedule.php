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
 * Subscription schedule resource model.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Resource_Schedule extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct(){
		$this->_init('customweb_subscription/subscription_schedule', 'schedule_id');
	}

	/**
	 * If job is currently in $currentStatus, set it to $newStatus
	 * and return true.
	 * Otherwise, return false and do not change the job.
	 * This method is used to implement locking for jobs.
	 *
	 * @param integer $scheduleId
	 * @param string $newStatus
	 * @param string $currentStatus
	 * @return boolean
	 */
	public function trySetJobStatusAtomic($scheduleId, $newStatus, $currentStatus){
		$write = $this->_getWriteAdapter();
		$result = $write->update($this->getTable('customweb_subscription/subscription_schedule'), array(
			'status' => $newStatus
		), array(
			'schedule_id = ?' => $scheduleId,
			'status = ?' => $currentStatus
		));
		if ($result == 1) {
			return true;
		}
		return false;
	}

	/**
	 * Delete manual schedules by subscription id.
	 *
	 * @param int $subscriptionId
	 */
	public function deleteManualBySubscription($subscriptionId){
		$write = $this->_getWriteAdapter();
		$condition = array(
			$write->quoteInto('subscription_id = ?', $subscriptionId),
			$write->quoteInto('action = ?', Customweb_Subscription_Model_Subscription_Schedule::ACTION_PAY_MANUAL)
		);
		$write->delete($this->getTable('customweb_subscription/subscription_schedule'), $condition);
		$condition = array(
			$write->quoteInto('subscription_id = ?', $subscriptionId),
			$write->quoteInto('action = ?', Customweb_Subscription_Model_Subscription_Schedule::ACTION_CHECK_MANUAL)
		);
		$write->delete($this->getTable('customweb_subscription/subscription_schedule'), $condition);
	}

	/**
	 * Delete schedules by subscription id.
	 *
	 * @param int $subscriptionId
	 */
	public function deleteBySubscription($subscriptionId){
		$write = $this->_getWriteAdapter();
		$condition = array(
			$write->quoteInto('subscription_id = ?', $subscriptionId),
			$write->quoteInto('status = ?', Customweb_Subscription_Model_Schedule::STATUS_PENDING)
		);
		$write->delete($this->getTable('customweb_subscription/subscription_schedule'), $condition);
	}

	/**
	 * Delete schedules by status and date.
	 *
	 * @param string $status
	 * @param Zend_Date $date
	 */
	public function deleteByStatusAndDate($status, Zend_Date $date){
		$write = $this->_getWriteAdapter();
		$condition = array(
			$write->quoteInto('status = ?', $status),
			'finished_at IS NOT NULL',
			$write->quoteInto('finished_at < ?', Mage::helper('customweb_subscription')->toDateString($date))
		);
		$write->delete($this->getTable('customweb_subscription/subscription_schedule'), $condition);
	}
}