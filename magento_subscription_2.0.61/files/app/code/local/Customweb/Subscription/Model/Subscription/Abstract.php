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
 * Represents a subscription cancellation.
 *
 * @author Simon Schurter
 */
abstract class Customweb_Subscription_Model_Subscription_Abstract implements Customweb_Subscription_Model_ISubscription {

	/**
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	private $_subscription = null;

	/**
	 *
	 * @var Customweb_Subscription_Model_Logger
	 */
	private $_logger = null;

	/**
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 */
	public function __construct(Customweb_Subscription_Model_Subscription $subscription){
		$this->_subscription = $subscription;
	}

	/**
	 * @return Customweb_Subscription_Model_Logger
	 */
	protected function getLogger(){
		if ($this->_logger == null) {
			$this->_logger = new Customweb_Subscription_Model_Logger($this->getSubscription());
		}
		return $this->_logger;
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	protected function getSubscription(){
		return $this->_subscription;
	}

	/**
	 * Check whether subscription can be changed to specified status.
	 *
	 * @param string $againstStatus
	 * @param boolean $soft
	 * @return boolean
	 * @throws Mage_Core_Exception
	 */
	protected function checkWorkflow($againstStatus, $soft = true){
		return $this->getSubscription()->checkWorkflow($againstStatus, $soft);
	}

	/**
	 * Return the subscription's current status.
	 *
	 * @return string
	 */
	protected function getStatus(){
		return $this->getSubscription()->getStatus();
	}

	/**
	 * Change the subscription's status.
	 *
	 * @param string $status
	 * @return Customweb_Subscription_Model_Subscription_Abstract
	 */
	protected function setStatus($status){
		$this->getSubscription()->setStatus($status);
		return $this;
	}

	/**
	 * Save the subscription to the database.
	 *
	 * @return Customweb_Subscription_Model_Subscription_Abstract
	 */
	protected function save(){
		$this->getSubscription()->save();
		return $this;
	}

	/**
	 * Get resource instance
	 *
	 * @return Mage_Core_Model_Mysql4_Abstract
	 */
	protected function _getResource(){
		return $this->getSubscription()->getResource();
	}
}