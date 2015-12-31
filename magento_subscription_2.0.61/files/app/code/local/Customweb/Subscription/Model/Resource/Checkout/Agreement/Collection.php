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
class Customweb_Subscription_Model_Resource_Checkout_Agreement_Collection extends Mage_Checkout_Model_Resource_Agreement_Collection {

	protected function _afterLoad(){
		parent::_afterLoad();
		
		if (Mage::getStoreConfig('customweb_subscription/checkout/enabled_agreement') &&
				 Mage::helper('customweb_subscription/cart')->isQuoteRecurring()) {
			$this->addItem($this->getSubscriptionAgreement());
		}
		
		return $this;
	}

	public function getAllIds(){
		$ids = parent::getAllIds();
		
		if (Mage::getStoreConfig('customweb_subscription/checkout/enabled_agreement') &&
				 Mage::helper('customweb_subscription/cart')->isQuoteRecurring()) {
			$ids[] = 'subscription';
		}
		return $ids;
	}

	protected function getSubscriptionAgreement(){
		$agreement = Mage::getModel('checkout/agreement')->setData(
				array(
					'name' => 'Subscription Agreement',
					'content' => Mage::app()->getLayout()->createBlock('customweb_subscription/checkout_agreements_subscription')->toHtml(),
					'content_height' => 'auto',
					'checkbox_text' => Mage::getStoreConfig('customweb_subscription/checkout/agreement_checkbox_text'),
					'is_active' => 1,
					'is_html' => 1 
				))->setId('subscription');
		return $agreement;
	}
}