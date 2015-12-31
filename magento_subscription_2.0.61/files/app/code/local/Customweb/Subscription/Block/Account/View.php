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
class Customweb_Subscription_Block_Account_View extends Mage_Core_Block_Template {
	protected $_subscription = null;
	protected $_info = array();
	protected $_relatedOrders = null;

	protected function _prepareRelatedOrders($fieldsToSelect = '*'){
		if (null === $this->_relatedOrders) {
			$this->_relatedOrders = Mage::getResourceModel('sales/order_collection')->addFieldToSelect($fieldsToSelect)->addFieldToFilter(
					'customer_id', Mage::registry('current_customer')->getId())->addAttributeToFilter('entity_id', 
					array(
						'in' => $this->_subscription->getChildOrderIds() 
					))->setOrder('entity_id', 'desc');
		}
	}

	public function prepareRelatedOrdersFrontendGrid(){
		$this->_prepareRelatedOrders(
				array(
					'increment_id',
					'created_at',
					'customer_firstname',
					'customer_lastname',
					'base_grand_total',
					'status' 
				));
		$this->_relatedOrders->addFieldToFilter('state', 
				array(
					'in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates() 
				));
		
		$pager = $this->getLayout()->createBlock('page/html_pager')->setCollection($this->_relatedOrders)->setIsOutputRequired(false);
		$this->setChild('pager', $pager);
		
		$this->setGridColumns(
				array(
					new Varien_Object(
							array(
								'index' => 'increment_id',
								'title' => $this->__('Order #'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(
							array(
								'index' => 'created_at',
								'title' => $this->__('Date'),
								'is_nobr' => true,
								'width' => 1 
							)),
					new Varien_Object(array(
						'index' => 'customer_name',
						'title' => $this->__('Customer Name') 
					)),
					new Varien_Object(
							array(
								'index' => 'base_grand_total',
								'title' => $this->__('Order Total'),
								'is_nobr' => true,
								'width' => 1,
								'is_amount' => true 
							)),
					new Varien_Object(
							array(
								'index' => 'status',
								'title' => $this->__('Order Status'),
								'is_nobr' => true,
								'width' => 1 
							)) 
				));
		
		$orders = array();
		foreach ($this->_relatedOrders as $order) {
			$orders[] = new Varien_Object(
					array(
						'increment_id' => $order->getIncrementId(),
						'created_at' => Mage::helper('customweb_subscription')->formatDate($order->getCreatedAt()),
						'customer_name' => $order->getCustomerName(),
						'base_grand_total' => Mage::helper('core')->formatCurrency($order->getBaseGrandTotal(), false),
						'status' => $order->getStatusLabel(),
						'increment_id_link_url' => $this->getUrl('sales/order/view/', array(
							'order_id' => $order->getId() 
						)) 
					));
		}
		if ($orders) {
			$this->setGridElements($orders);
		}
	}

	protected function _prepareLayout(){
		$this->_subscription = Mage::registry('current_subscription');
		return parent::_prepareLayout();
	}

	public function getSubscription(){
		return $this->_subscription;
	}

	protected function _toHtml(){
		if (!$this->_subscription) {
			return '';
		}
		
		foreach ($this->getChildGroup('info_tabs') as $block) {
			$block->setViewUrl($this->getUrl("*/*/{$block->getViewAction()}", array(
				'subscription_id' => $this->_subscription->getId() 
			)));
		}
		
		return parent::_toHtml();
	}

	public function getCancelUrl(){
		return Mage::getUrl('*/*/cancel', array(
			'subscription_id' => $this->getSubscription()->getId() 
		));
	}

	public function getSuspendUrl(){
		return Mage::getUrl('*/*/suspend', array(
			'subscription_id' => $this->getSubscription()->getId() 
		));
	}

	public function getActivateUrl(){
		return Mage::getUrl('*/*/activate', array(
			'subscription_id' => $this->getSubscription()->getId() 
		));
	}

	public function getBackUrl(){
		return Mage::getUrl('*/*');
	}

	public function getPaymentUrl(){
		return Mage::getUrl('*/payment/index', array(
			'subscription_id' => $this->getSubscription()->getId() 
		));
	}
}
