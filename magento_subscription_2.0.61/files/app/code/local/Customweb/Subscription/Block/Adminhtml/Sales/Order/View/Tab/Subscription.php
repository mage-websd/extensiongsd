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
class Customweb_Subscription_Block_Adminhtml_Sales_Order_View_Tab_Subscription extends Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Info {
	protected $_subscription = null;

	public function getOrder(){
		return Mage::registry('current_order');
	}

	public function getSubscription(){
		if ($this->_subscription == null) {
			$this->_subscription = Mage::helper('customweb_subscription')->getSubscriptionByOrder($this->getOrder());
		}
		return $this->_subscription;
	}

	/**
	 * ######################## TAB settings #################################
	 */
	public function getTabLabel(){
		return Mage::helper('customweb_subscription')->__('Subscription');
	}

	public function getTabTitle(){
		return Mage::helper('customweb_subscription')->__('Subscription');
	}

	public function canShowTab(){
		return $this->getSubscription() != null;
	}

	public function isHidden(){
		return false;
	}
}
