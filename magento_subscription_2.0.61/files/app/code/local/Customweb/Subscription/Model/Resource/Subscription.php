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
 * Subscription resource model.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Resource_Subscription extends Customweb_Subscription_Model_Resource_Abstract {
	
	/**
	 * Field to be serialized when saving.
	 *
	 * @var array
	 */
	protected $_serializableFields = array(
		'order_info' => array(
			null,
			array() 
		),
		'order_item_info' => array(
			null,
			array() 
		),
		'billing_address_info' => array(
			null,
			array() 
		),
		'shipping_address_info' => array(
			null,
			array() 
		) 
	);

	protected function _construct(){
		$this->_init('customweb_subscription/subscription', 'entity_id');
	}

	/**
	 * Return subscription's child orders ids.
	 *
	 * @param Customweb_Subscription_Model_Subscription $object
	 * @return array
	 */
	public function getChildOrderIds(Customweb_Subscription_Model_Subscription $object){
		$adapter = $this->_getReadAdapter();
		$bind = array(
			':subscription_id' => $object->getId() 
		);
		$select = $adapter->select()->from(array(
			'main_table' => $this->getTable('customweb_subscription/subscription_order') 
		), array(
			'order_id' 
		))->where('subscription_id=:subscription_id');
		
		return $adapter->fetchCol($select, $bind);
	}

	/**
	 * Add order relation to subscription.
	 *
	 * @param int $subscriptionId
	 * @param int $orderId
	 * @return Customweb_Subscription_Model_Resource_Subscription
	 */
	public function addOrderRelation($subscriptionId, $orderId){
		$this->_getWriteAdapter()->insert($this->getTable('customweb_subscription/subscription_order'), 
				array(
					'subscription_id' => $subscriptionId,
					'order_id' => $orderId 
				));
		return $this;
	}
}