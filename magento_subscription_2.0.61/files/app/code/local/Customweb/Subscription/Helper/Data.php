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
class Customweb_Subscription_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return Customweb_Subscription_Model_Subscription|null
	 */
	public function getSubscriptionByOrder(Mage_Sales_Model_Order $order){
		$link = Mage::getModel('customweb_subscription/order')->load($order->getId(), 'order_id');
		if ($link->getId()) {
			return Mage::getModel('customweb_subscription/subscription')->load($link->getSubscriptionId());
		}
		else {
			return null;
		}
	}

	/**
	 *
	 * @param string $date
	 * @return Zend_Date
	 */
	public function toDateObject($date){
		return new Zend_Date($date, Varien_Date::DATETIME_INTERNAL_FORMAT);
		// 		return Mage::app()->getLocale()->date(Varien_Date::toTimestamp($date));
	}

	/**
	 *
	 * @param Zend_Date $date
	 * @return string
	 */
	public function toDateString(Zend_Date $date){
		return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
	}

	/**
	 *
	 * @param string|Zend_Date|null $date
	 * @param string $format
	 * @param boolean $showTime
	 */
	public function formatDate($date = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showTime = false) {
		if ($date instanceof Zend_Date) {
			$date = $this->toDateString($date);
		}
		return Mage::helper('core')->formatDate($date, $format, $showTime);
	}

	/**
	 * Getter for available statuses.
	 *
	 * @param boolean $withLabels
	 * @return array
	 */
	public function getAllStatuses($withLabels = true){
		$statuses = array(
			Customweb_Subscription_Model_Subscription::STATUS_UNKNOWN,
			Customweb_Subscription_Model_Subscription::STATUS_ACTIVE,
			Customweb_Subscription_Model_Subscription::STATUS_PENDING,
			Customweb_Subscription_Model_Subscription::STATUS_SUSPENDED,
			Customweb_Subscription_Model_Subscription::STATUS_CANCELED,
			Customweb_Subscription_Model_Subscription::STATUS_FAILED,
			Customweb_Subscription_Model_Subscription::STATUS_ERROR,
			Customweb_Subscription_Model_Subscription::STATUS_EXPIRED,
			Customweb_Subscription_Model_Subscription::STATUS_AUTHORIZED,
			Customweb_Subscription_Model_Subscription::STATUS_PAID
		);

		if ($withLabels) {
			$result = array();
			foreach ($statuses as $status) {
				$result[$status] = Mage::helper('customweb_subscription/render')->getStatusLabel($status);
			}
			return $result;
		}
		return $statuses;
	}

	/**
	 * Get a list of schedule statuses.
	 *
	 * @return array
	 */
	public function getScheduleStatuses(){
		return array(
			Customweb_Subscription_Model_Schedule::STATUS_PENDING => $this->__('Pending'),
			Customweb_Subscription_Model_Schedule::STATUS_RUNNING => $this->__('Running'),
			Customweb_Subscription_Model_Schedule::STATUS_SUCCESS => $this->__('Success'),
			Customweb_Subscription_Model_Schedule::STATUS_ERROR => $this->__('Error'),
		);
	}

	/**
	 *
	 * @param int $storeId
	 * @throws Exception
	 * @return array
	 */
	public function getPaytime($storeId = null){
		$config = unserialize(base64_decode(Mage::getStoreConfig('customweb_subscription/general/paytime', $storeId)));
		if (!is_array($config)) {
			throw new Exception('No paytime has been defined.');
		}
		return $config;
	}
}