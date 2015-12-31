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
 * Represents a subscription plan.
 *
 * @author Simon Schurter
 *
 * @method Customweb_Subscription_Model_PeriodUnit getPeriodUnit()
 * @method Customweb_Subscription_Model_Plan setPeriodUnit(Customweb_Subscription_Model_PeriodUnit $value)
 * @method int getPeriodFrequency()
 * @method Customweb_Subscription_Model_Plan setPeriodFrequency(int $value)
 * @method int getPeriodMaxCycles()
 * @method Customweb_Subscription_Model_Plan setPeriodMaxCycles(int $value)
 * @method int getStoreId()
 * @method Customweb_Subscription_Model_Plan setStoreId(int $value)
 * @method Customweb_Subscription_Model_Subscription getSubscription()
 * @method Customweb_Subscription_Model_Plan setSubscription(Customweb_Subscription_Model_Subscription $value)
 */
class Customweb_Subscription_Model_Plan extends Mage_Core_Model_Abstract {

	/**
	 *
	 * @param Customweb_Subscription_Model_Subscription $subscription
	 * @return Customweb_Subscription_Model_Plan
	 */
	public function fromSubscription(Customweb_Subscription_Model_Subscription $subscription){
		$this->setPeriodUnit(Customweb_Subscription_Model_PeriodUnit::valueOf($subscription->getPeriodUnit()));
		$this->setPeriodFrequency($subscription->getPeriodFrequency());
		$this->setPeriodMaxCycles($subscription->getPeriodMaxCycles());
		$this->setStoreId($subscription->getStoreId());
		$this->setSubscription($subscription);
		return $this;
	}

	/**
	 *
	 * @param array $array
	 * @throws Exception
	 * @return Customweb_Subscription_Model_Plan
	 */
	public function fromArray(array $array){
		if (!isset($array['period_unit'])) {
			throw new Exception('Customweb_Subscription_Model_Plan::fromArray - No period unit set.');
		}
		$this->setPeriodUnit(Customweb_Subscription_Model_PeriodUnit::valueOf($array['period_unit']));

		if (!isset($array['period_frequency'])) {
			throw new Exception('Customweb_Subscription_Model_Plan::fromArray - No period frequency set.');
		}
		$this->setPeriodFrequency($array['period_frequency']);

		if (!isset($array['period_max_cycles'])) {
			throw new Exception('Customweb_Subscription_Model_Plan::fromArray - No period max cycles set.');
		}
		$this->setPeriodMaxCycles($array['period_max_cycles']);

		if (isset($array['store_id'])) {
			$this->setStoreId($array['store_id']);
		}
		return $this;
	}

	/**
	 * Calculate the next date on which the subscription is to be executed.
	 *
	 * @throws Exception
	 * @return Zend_Date
	 */
	public function getNextDueDate(){
		if ($this->getSubscription() == null) {
			throw new Exception('No subscription has been set.');
		}
		if ($this->getPeriodMaxCycles() == null || $this->getSubscription()->getNumberOfCycles() < $this->getPeriodMaxCycles()) {
			$nowDate = Zend_Date::now();
			$fromDateString = $this->getSubscription()->getLastRegularDatetime();
			if ($fromDateString == null) {
				$fromDateString = $this->getSubscription()->getStartDatetime();
			}
			$resultDate = Mage::helper('customweb_subscription')->toDateObject($fromDateString);
			do {
				$this->nextDueDate($resultDate);
			}
			while ($nowDate->compare($resultDate) >= 0);
			return $resultDate;
		}
		throw new Exception('The subscription cannot be executed.');
	}

	/**
	 *
	 * @param Zend_Date $date
	 */
	public function previousDueDate(Zend_Date $date){
		$date->sub($this->getPeriodFrequency(), $this->getPeriodUnit()->getDateConstant());
	}

	/**
	 *
	 * @param Zend_Date $date
	 */
	public function nextDueDate(Zend_Date $date){
		$date->add($this->getPeriodFrequency(), $this->getPeriodUnit()->getDateConstant());
	}
}