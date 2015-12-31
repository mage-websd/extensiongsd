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
class Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Orders extends Mage_Adminhtml_Block_Widget_Grid implements 
		Mage_Adminhtml_Block_Widget_Tab_Interface {

	protected function _construct(){
		parent::_construct();
		$this->setId('customweb_subscription_orders');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSkipGenerateContent(true);
	}

	/**
	 * Prepare grid collection object
	 *
	 * @return Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Orders
	 */
	protected function _prepareCollection(){
		$collection = Mage::getResourceModel('sales/order_grid_collection')->addAttributeToFilter('entity_id', 
				array(
					'in' => $this->getSubscription()->getChildOrderIds() 
				));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 * Prepare grid columns
	 *
	 * @return Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Orders
	 */
	protected function _prepareColumns(){
		$this->addColumn('real_order_id', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('Order #'),
					'width' => '80px',
					'type' => 'text',
					'index' => 'increment_id' 
				));
		
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', 
					array(
						'header' => Mage::helper('customweb_subscription')->__('Purchased From (Store)'),
						'index' => 'store_id',
						'type' => 'store',
						'store_view' => true,
						'display_deleted' => true 
					));
		}
		
		$this->addColumn('created_at', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('Purchased On'),
					'index' => 'created_at',
					'type' => 'datetime',
					'width' => '100px' 
				));
		
		$this->addColumn('billing_name', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('Bill to Name'),
					'index' => 'billing_name' 
				));
		
		$this->addColumn('shipping_name', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('Ship to Name'),
					'index' => 'shipping_name' 
				));
		
		$this->addColumn('base_grand_total', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('G.T. (Base)'),
					'index' => 'base_grand_total',
					'type' => 'currency',
					'currency' => 'base_currency_code' 
				));
		
		$this->addColumn('grand_total', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('G.T. (Purchased)'),
					'index' => 'grand_total',
					'type' => 'currency',
					'currency' => 'order_currency_code' 
				));
		
		$this->addColumn('status', 
				array(
					'header' => Mage::helper('customweb_subscription')->__('Status'),
					'index' => 'status',
					'type' => 'options',
					'width' => '70px',
					'options' => Mage::getSingleton('sales/order_config')->getStatuses() 
				));
		
		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			$this->addColumn('action', 
					array(
						'header' => Mage::helper('customweb_subscription')->__('Action'),
						'width' => '50px',
						'type' => 'action',
						'getter' => 'getId',
						'actions' => array(
							array(
								'caption' => Mage::helper('customweb_subscription')->__('View'),
								'url' => array(
									'base' => 'adminhtml/sales_order/view' 
								),
								'field' => 'order_id' 
							) 
						),
						'filter' => false,
						'sortable' => false,
						'index' => 'stores',
						'is_system' => true 
					));
		}
		
		return parent::_prepareColumns();
	}

	/**
	 * Retrieve subscription model instance
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		return Mage::registry('current_subscription');
	}

	public function getRowUrl($row){
		return $this->getUrl('adminhtml/sales_order/view', array(
			'order_id' => $row->getId() 
		));
	}

	public function getGridUrl(){
		return $this->getTabUrl();
	}

	/**
	 * ######################## TAB settings #################################
	 */
	public function getTabUrl(){
		return $this->getUrl('*/*/orders', array(
			'_current' => true 
		));
	}

	public function getTabClass(){
		return 'ajax';
	}

	public function getTabLabel(){
		return Mage::helper('customweb_subscription')->__('Related Orders');
	}

	public function getTabTitle(){
		return Mage::helper('customweb_subscription')->__('Related Orders');
	}

	public function canShowTab(){
		return true;
	}

	public function isHidden(){
		return false;
	}
}
