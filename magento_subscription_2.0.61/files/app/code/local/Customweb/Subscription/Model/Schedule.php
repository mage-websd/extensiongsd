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
 * Represents a scheduled subscription.
 *
 * @author Simon Schurter
 *
 * @method int getId()
 * @method int getSubscriptionId()
 * @method Customweb_Subscription_Model_Schedule setSubscriptionId(int $value)
 * @method string getStatus()
 * @method Customweb_Subscription_Model_Schedule setStatus(string $value)
 * @method string getMessages()
 * @method Customweb_Subscription_Model_Schedule setMessages(string $value)
 * @method string getCreatedAt()
 * @method Customweb_Subscription_Model_Schedule setCreatedAt(string $value)
 * @method string getScheduledAt()
 * @method Customweb_Subscription_Model_Schedule setScheduledAt(string $value)
 * @method string getExecutedAt()
 * @method Customweb_Subscription_Model_Schedule setExecutedAt(string $value)
 * @method string getFinishedAt()
 * @method Customweb_Subscription_Model_Schedule setFinishedAt(string $value)
 * @method int getCount()
 * @method Customweb_Subscription_Model_Schedule setCount(int $value)
 * @method string getAction()
 * @method Customweb_Subscription_Model_Schedule setAction(string $value)
 * @method string getAdditionalData()
 * @method Customweb_Subscription_Model_Schedule setAdditionalData(string $value)
 */
class Customweb_Subscription_Model_Schedule extends Mage_Core_Model_Abstract {
	
	/**
	 * Pending: Pending state is set when the item is scheduled to be executed.
	 */
	const STATUS_PENDING = 'pending';
	
	/**
	 * Running: Running is set when the job is currently in the execution. Means the server is currently processing the item.
	 */
	const STATUS_RUNNING = 'running';

	/**
	 * Success: Success is set when item was executed successfully.
	 */
	const STATUS_SUCCESS = 'success';
	
	/**
	 * Error: Error is set when the item could not be completed successfully. The number of retries exceeded.
	 */
	const STATUS_ERROR = 'error';
	
	/**
	 * Event prefix and object
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'customweb_subscription_schedule';
	protected $_eventObject = 'schedule';
	
	/**
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	private $_subscription = null;

	protected function _construct(){
		$this->_init('customweb_subscription/schedule');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		if ($this->_subscription == null) {
			$this->_subscription = Mage::getModel('customweb_subscription/subscription')->load($this->getSubscriptionId());
		}
		return $this->_subscription;
	}

	/**
	 * Sets a job to STATUS_RUNNING only if it is currently in STATUS_PENDING.
	 * Returns true if status was changed and false otherwise.
	 *
	 * @param $oldStatus This is used to implement locking for cron jobs.
	 * @return boolean
	 */
	public function tryLockJob($oldStatus = self::STATUS_PENDING){
		$this->setStatus(self::STATUS_RUNNING);
		return $this->_getResource()->trySetJobStatusAtomic($this->getId(), self::STATUS_RUNNING, $oldStatus);
	}
}