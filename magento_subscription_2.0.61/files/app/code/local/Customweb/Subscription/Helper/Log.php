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
class Customweb_Subscription_Helper_Log extends Mage_Core_Helper_Abstract {

	/**
	 * Get a list of log levels.
	 *
	 * @return array
	 */
	public function getLevels(){
		return array(
			Customweb_Subscription_Model_Log::LEVEL_DEBUG => Mage::helper('customweb_subscription')->__('Debug'),
			Customweb_Subscription_Model_Log::LEVEL_INFO => Mage::helper('customweb_subscription')->__('Info'),
			Customweb_Subscription_Model_Log::LEVEL_WARN => Mage::helper('customweb_subscription')->__('Warn'),
			Customweb_Subscription_Model_Log::LEVEL_ERROR => Mage::helper('customweb_subscription')->__('Error'),
		);
	}

	/**
	 * Clean up log entries.
	 */
	public function cleanUp(){
		$date = Zend_Date::now();
		$date->sub(2, Zend_Date::MONTH);
		Mage::getModel('customweb_subscription/log')->getResource()->deleteByDate($date);
	}

}