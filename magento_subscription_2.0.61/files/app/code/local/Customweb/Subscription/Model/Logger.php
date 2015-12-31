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
class Customweb_Subscription_Model_Logger {

	private $subscription;

	/**
	 * Constructor
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 */
	public function __construct($subscription){
		$this->subscription = $subscription;
	}

	/**
	 * Write a new debug log entry.
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @param string $message
	 * @param array $parameters
	 */
	public function debug($message, $parameters = array()){
		$this->log($message, $parameters, Customweb_Subscription_Model_Log::LEVEL_DEBUG);
	}

	/**
	 * Write a new info log entry.
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @param string $message
	 * @param array $parameters
	 */
	public function info($message, $parameters = array()){
		$this->log($message, $parameters, Customweb_Subscription_Model_Log::LEVEL_INFO);
	}

	/**
	 * Write a new warn log entry.
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @param string $message
	 * @param array $parameters
	 */
	public function warn($message, $parameters = array()){
		$this->log($message, $parameters, Customweb_Subscription_Model_Log::LEVEL_WARN);
	}

	/**
	 * Write a new error log entry.
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @param string $message
	 * @param array $parameters
	 */
	public function error($message, $parameters = array()){
		$this->log($message, $parameters, Customweb_Subscription_Model_Log::LEVEL_ERROR);
	}

	/**
	 * Write a new log entry.
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @param string $message
	 * @param array $parameters
	 * @param string $level
	 */
	public function log($message, $parameters = array(), $level){
		if (!$this->isLevelEnabled($level)) {
			return;
		}

		$log = Mage::getModel('customweb_subscription/log');
		$log->setLevel($level);
		$log->setMessage($message);
		$log->setParameters($parameters);
		$log->setSubscriptionId($this->subscription->getId());
		$log->setCreatedAt(Mage::helper('customweb_subscription')->toDateString(Zend_Date::now()));

		$transaction = Mage::getModel('core/resource_transaction');
		$transaction->addObject($log);
		$transaction->save();
	}

	private function isLevelEnabled($level){
		$enabledLevels = explode(',', Mage::getStoreConfig('customweb_subscription/general/log_level'));
		if (empty($enabledLevels)) {
			return false;
		} else {
			return in_array($level, $enabledLevels);
		}
	}

}