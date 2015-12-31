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
class Customweb_Subscription_Block_Account_Grid extends Mage_Core_Block_Template {
	protected $_subscriptions = null;

	public function prepareSubscriptionsGrid(){
		$this->_prepareSubscriptions();
		
		$pager = $this->getLayout()->createBlock('page/html_pager')->setCollection($this->_subscriptions)->setIsOutputRequired(false);
		$this->setChild('pager', $pager);
		
		$this->setGridColumns(
				array(
					new Varien_Object(
							array(
								'index' => 'reference_id',
								'title' => Mage::helper('customweb_subscription')->__('Reference Id'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(
							array(
								'index' => 'status',
								'title' => Mage::helper('customweb_subscription')->__('Status') 
							)),
					new Varien_Object(
							array(
								'index' => 'start_date',
								'title' => Mage::helper('customweb_subscription')->__('Start Date'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(
							array(
								'index' => 'last_date',
								'title' => Mage::helper('customweb_subscription')->__('Last Billed'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(
							array(
								'index' => 'next_date',
								'title' => Mage::helper('customweb_subscription')->__('Next Cycle'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(
							array(
								'index' => 'method_code',
								'title' => Mage::helper('customweb_subscription')->__('Payment Method'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(array(
						'index' => 'actions',
						'width' => 1 
					)) 
				));
		
		$subscriptions = array();
		$store = Mage::app()->getStore();
		$locale = Mage::app()->getLocale();
		foreach ($this->_subscriptions as $subscription) {
			$subscription->setStore($store)->setLocale($locale);
			$subscriptions[] = new Varien_Object(
					array(
						'reference_id' => $subscription->getReferenceId(),
						'reference_id_link_url' => $this->getUrl('subscription/index/view/', 
								array(
									'subscription_id' => $subscription->getId() 
								)),
						'status' => $subscription->getStatus(),
						'start_date' => $this->formatDate($subscription->getStartDatetime(), 'medium', true),
						'last_date' => $this->formatDate($subscription->getLastDatetime(), 'medium', true),
						'next_date' => Mage::helper('customweb_subscription/render')->renderNextDueDate($subscription->getPlan()),
						'method_code' => $subscription->getMethodInstance()->getTitle(),
						'actions' => $this->_getActions($subscription) 
					));
		}
		if ($subscriptions) {
			$this->setGridElements($subscriptions);
		}
		$orders = array();
	}

	protected function _getActions($subscription){
		$html = '';
		
		if ($subscription->canPay() && $subscription->canPayOnline()) {
			$url = Mage::getUrl('*/payment/index', array(
				'subscription_id' => $subscription->getId() 
			));
			$html .= '<button type="button" title="' . Mage::helper('customweb_subscription')->__('Pay') . '" class="button" onclick="window.location.href = \'' . $url .
					 '\'; return false;"><span><span>' . Mage::helper('customweb_subscription')->__('Pay') . '</span></span></button>';
		}
		
		return $html;
	}

	protected function _prepareSubscriptions($fields = '*'){
		$this->_subscriptions = Mage::getModel('customweb_subscription/subscription')->getCollection()->addFieldToFilter('customer_id', 
				Mage::registry('current_customer')->getId())->addFieldToSelect($fields)->setOrder('entity_id', 'desc');
	}

	protected function _beforeToHtml(){
		$this->setBackUrl($this->getUrl('customer/account/'));
		return parent::_beforeToHtml();
	}
}
