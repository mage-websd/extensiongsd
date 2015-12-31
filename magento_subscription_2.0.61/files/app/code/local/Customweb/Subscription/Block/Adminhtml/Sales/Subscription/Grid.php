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
class Customweb_Subscription_Block_Adminhtml_Sales_Subscription_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct(){
		parent::__construct();
		$this->setId('customweb_subscription_sales_subscription_grid');
		$this->setUseAjax(true);
		$this->setDefaultSort('start_datetime');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setGridHeader(Mage::helper('customweb_subscription')->__('Subscriptions'));

		if ($statusFilter = Mage::app()->getRequest()->getParam('status_filter')) {
			$this->setDefaultFilter(array(
				'status' => $statusFilter
			));
		}
	}

	protected function _prepareCollection(){
		$collection = Mage::getResourceModel('customweb_subscription/subscription_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns(){
		$this->addColumn('reference_id',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Reference Id'),
					'index' => 'reference_id',
					'html_decorators' => array(
						'nobr'
					),
					'width' => 1
				));

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id',
					array(
						'header' => Mage::helper('adminhtml')->__('Store'),
						'index' => 'store_id',
						'type' => 'store',
						'store_view' => true,
						'display_deleted' => true
					));
		}

		$this->addColumn('status',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Status'),
					'index' => 'status',
					'type' => 'options',
					'options' => Mage::helper('customweb_subscription')->getAllStatuses(),
					'html_decorators' => array(
						'nobr'
					),
					'width' => 1
				));

		$this->addColumn('start_datetime',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Started At'),
					'index' => 'start_datetime',
					'type' => 'datetime',
					'html_decorators' => array(
						'nobr'
					),
					'width' => 1
				));

		$this->addColumn('last_datetime',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Last Billed'),
					'index' => 'last_datetime',
					'type' => 'datetime',
					'html_decorators' => array(
						'nobr'
					),
					'width' => 1
				));

		$this->addColumn('subscriber_name',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Subscriber'),
					'index' => 'subscriber_name',
					'html_decorators' => array(
						'nobr'
					)
				));

		$methods = array();
		foreach (Mage::helper('customweb_subscription/payment')->getActivePaymentMethods() as $method) {
			$methods[$method->getCode()] = $method->getTitle();
		}
		$this->addColumn('method_code',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Payment Method'),
					'index' => 'method_code',
					'type' => 'options',
					'options' => $methods
				));

		$this->addColumn('description',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Description'),
					'index' => 'description',
					'filter' => false,
					'sortable' => false
				));

		$this->addColumn('action',
				array(
					'header' => Mage::helper('sales')->__('Action'),
					'width' => '50px',
					'type' => 'action',
					'getter' => 'getEntityId',
					'actions' => array(
						array(
							'caption' => Mage::helper('customweb_subscription')->__('View'),
							'url' => array(
								'base' => '*/*/view'
							),
							'field' => 'subscription_id'
						)
					),
					'filter' => false,
					'sortable' => false,
					'index' => 'stores',
					'is_system' => true
				));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction(){
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('subscription_ids');

		$this->getMassactionBlock()->addItem('subscription_activate',
				array(
					'label' => Mage::helper('customweb_subscription')->__('Activate'),
					'url' => $this->getUrl('adminhtml/subscription/batchStatus/action/activate')
				));

		$this->getMassactionBlock()->addItem('subscription_authorize',
				array(
					'label' => Mage::helper('customweb_subscription')->__('Activate & Request Payment'),
					'url' => $this->getUrl('adminhtml/subscription/batchAuthorize')
				));

		$this->getMassactionBlock()->addItem('subscription_suspend',
				array(
					'label' => Mage::helper('customweb_subscription')->__('Suspend'),
					'url' => $this->getUrl('adminhtml/subscription/batchStatus/action/suspend')
				));

		$this->getMassactionBlock()->addItem('subscription_cancel',
				array(
					'label' => Mage::helper('customweb_subscription')->__('Cancel'),
					'url' => $this->getUrl('adminhtml/subscription/batchStatus/action/cancel')
				));

		$this->getMassactionBlock()->addItem('subscription_delete',
				array(
					'label' => Mage::helper('customweb_subscription')->__('Delete'),
					'url' => $this->getUrl('adminhtml/subscription/batchDelete')
				));

		return $this;
	}

	public function getRowUrl($row){
		return $this->getUrl('*/*/view', array(
			'subscription_id' => $row->getEntityId()
		));
	}

	public function getGridUrl(){
		return $this->getUrl('*/*/grid', array(
			'_current' => true
		));
	}
}