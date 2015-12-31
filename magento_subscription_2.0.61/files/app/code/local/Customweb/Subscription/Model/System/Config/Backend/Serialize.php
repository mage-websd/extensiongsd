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
 * Backend configuration model that persists values serialized.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_System_Config_Backend_Serialize extends Mage_Core_Model_Config_Data {

	protected function _afterLoad(){
		if (!is_array($this->getValue())) {
			$value = $this->getValue();
			$this->setValue(empty($value) ? false : unserialize(base64_decode($value)));
		}
	}

	protected function _beforeSave(){
		$value = $this->getValue();
		if (is_array($value)) {
			unset($value['__empty']);
			foreach ($value as $code => $field) {
				unset($value[$code]['#{value}']);
			}
		}
		$this->setValue($value);
		
		if (is_array($this->getValue())) {
			$this->setValue(base64_encode(serialize($this->getValue())));
		}
	}
}
