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
 *
 * @author Simon Schurter
 *
 * @method int getIndex()
 * @method Customweb_Subscription_Model_CartPlan setIndex(int $value)
 * @method string getDescription()
 * @method Customweb_Subscription_Model_CartPlan setDescription(string $value)
 * @method string getPeriodUnit()
 * @method Customweb_Subscription_Model_CartPlan setPeriodUnit(string $value)
 * @method int getPeriodFrequency()
 * @method Customweb_Subscription_Model_CartPlan setPeriodFrequency(int $value)
 * @method int getPeriodMaxCycles()
 * @method Customweb_Subscription_Model_CartPlan setPeriodMaxCycles(int $value)
 * @method float getInitAmount()
 * @method Customweb_Subscription_Model_CartPlan setInitAmount(float $value)
 * @method int getCancelPeriod()
 * @method Customweb_Subscription_Model_CartPlan setCancelPeriod(int $value)
 * @method boolean getCanCustomerSuspend()
 * @method Customweb_Subscription_Model_CartPlan setCanCustomerSuspend(boolean $value)
 * @method int getSortOrder()
 * @method Customweb_Subscription_Model_CartPlan setSortOrder(int $value)
 */
class Customweb_Subscription_Model_CartPlan extends Mage_Core_Model_Abstract {

	public function loadByIndex($index){
		$plans = Mage::helper('customweb_subscription/cart')->getCartSubscriptionPlans();
		foreach ($plans as $plan) {
			if ($plan->getIndex() == $index) {
				return $plan;
			}
		}
		return false;
	}

	public function getPlan(){
		return Mage::getModel('customweb_subscription/plan')->fromArray($this->getData());
	}

	public function getInformation(){
		return array(
			'description' => $this->getDescription(),
			'period_unit' => $this->getPeriodUnit(),
			'period_frequency' => $this->getPeriodFrequency(),
			'period_max_cycles' => $this->getPeriodMaxCycles(),
			'init_amount' => $this->getInitAmount(),
			'cancel_period' => $this->getCancelPeriod(),
			'can_customer_suspend' => $this->getCanCustomerSuspend() 
		);
	}
}