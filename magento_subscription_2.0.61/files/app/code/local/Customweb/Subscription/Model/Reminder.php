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
 * Represents a subscription reminder email.
 *
 * @author Simon Schurter
 *
 * @method int getCount()
 * @method Customweb_Subscription_Model_Reminder setCount(int $value)
 * @method string getUnit()
 * @method Customweb_Subscription_Model_Reminder setUnit(string $value)
 * @method string getEmailTemplate()
 * @method Customweb_Subscription_Model_Reminder setEmailTemplate(string $value)
 */
class Customweb_Subscription_Model_Reminder extends Mage_Core_Model_Abstract {

	/**
	 *
	 * @param Zend_Date $dueDate
	 */
	public function getRemindDate(Zend_Date $dueDate){
		$remindDate = new Zend_Date($dueDate);
		$remindDate->sub($this->getCount(), Customweb_Subscription_Model_PeriodUnit::valueOf($this->getUnit())->getDateConstant());
		return $remindDate;
	}

	/**
	 *
	 * @param Customweb_Subscription_Model_Reminderown $o
	 * @return number
	 */
	public function compareTo(Customweb_Subscription_Model_Reminder $o){
		$unitCompare = Customweb_Subscription_Model_PeriodUnit::valueOf($this->getUnit())->compareTo(
				Customweb_Subscription_Model_PeriodUnit::valueOf($o->getUnit()));
		if ($unitCompare == 0) {
			return $this->getCount() - $o->getCount();
		}
		return $unitCompare;
	}
}