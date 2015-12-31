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
class Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Info extends Mage_Adminhtml_Block_Widget implements 
		Mage_Adminhtml_Block_Widget_Tab_Interface {

	/**
	 * Retrieve subscription model instance
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		return Mage::registry('current_subscription');
	}

	/**
	 * Retrieve source model instance
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSource(){
		return $this->getSubscription();
	}

	public function getInfoHtml(){
		return $this->getChildHtml('subscription_info');
	}

	public function getItemsHtml(){
		return $this->getChildHtml('subscription_items');
	}

	public function getScheduleHtml(){
		return $this->getChildHtml('subscription_schedule');
	}

	public function getPaymentHtml(){
		return $this->getChildHtml('subscription_payment');
	}

	public function getAddressHtml(){
		return $this->getChildHtml('subscription_address');
	}

	public function getBillingHtml(){
		return $this->getChildHtml('subscription_billing');
	}

	/**
	 * ######################## TAB settings #################################
	 */
	public function getTabLabel(){
		return Mage::helper('customweb_subscription')->__('Subscription Information');
	}

	public function getTabTitle(){
		return Mage::helper('customweb_subscription')->__('Subscription Information');
	}

	public function canShowTab(){
		return true;
	}

	public function isHidden(){
		return false;
	}
}