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
class Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

	/**
	 * Retrieve available subscription
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		if ($this->hasSubscription()) {
			return $this->getData('subscription');
		}
		if (Mage::registry('current_subscription')) {
			return Mage::registry('current_subscription');
		}
		if (Mage::registry('subscription')) {
			return Mage::registry('subscription');
		}
		Mage::throwException(Mage::helper('customweb_subscription')->__('Cannot get the subscription instance.'));
	}

	public function __construct(){
		parent::__construct();
		$this->setId('customweb_subscription_view_tabs');
		$this->setDestElementId('customweb_subscription_edit');
		$this->setTitle(Mage::helper('customweb_subscription')->__('Subscription View'));
	}
}