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
 * Subscription log resource model.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract {

	/**
	 * Field to be serialized when saving.
	 *
	 * @var array
	 */
	protected $_serializableFields = array(
		'parameters' => array(
			null,
			array()
		)
	);

	protected function _construct(){
		$this->_init('customweb_subscription/subscription_log', 'log_id');
	}

	/**
	 * Delete logs by subscription id.
	 *
	 * @param int $subscriptionId
	 */
	public function deleteBySubscription($subscriptionId){
		$write = $this->_getWriteAdapter();
		$condition = array(
			$write->quoteInto('subscription_id = ?', $subscriptionId)
		);
		$write->delete($this->getTable('customweb_subscription/subscription_log'), $condition);
	}

	/**
	 * Delete logs by date.
	 *
	 * @param Zend_Date $date
	 */
	public function deleteByDate(Zend_Date $date){
		$write = $this->_getWriteAdapter();
		$condition = array(
			$write->quoteInto('created_at < ?', Mage::helper('customweb_subscription')->toDateString($date))
		);
		$write->delete($this->getTable('customweb_subscription/subscription_log'), $condition);
	}
}