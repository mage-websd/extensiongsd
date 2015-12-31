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
class Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Logs extends Mage_Adminhtml_Block_Widget_Grid implements
		Mage_Adminhtml_Block_Widget_Tab_Interface {

	protected function _construct(){
		parent::_construct();
		$this->setId('customweb_subscription_logs');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSkipGenerateContent(true);
	}

	/**
	 * Prepare grid collection object
	 *
	 * @return Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Logs
	 */
	protected function _prepareCollection(){
		$collection = Mage::getResourceModel('customweb_subscription/log_collection')->addFilter('subscription_id',
				array(
					'eq' => $this->getSubscription()->getId()
				));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 * Prepare grid columns
	 *
	 * @return Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Tab_Logs
	 */
	protected function _prepareColumns(){
		$this->addColumn('created_at',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Created At'),
					'index' => 'created_at',
					'type' => 'datetime',
					'width' => '100px'
				));

		$this->addColumn('level',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Level'),
					'index' => 'level',
					'type' => 'options',
					'width' => '70px',
					'options' => Mage::helper('customweb_subscription/log')->getLevels(),
					'renderer' => 'Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Renderer_LogLevel',
				));

		$this->addColumn('message',
				array(
					'header' => Mage::helper('customweb_subscription')->__('Message'),
					'index' => 'message',
					'renderer' => 'Customweb_Subscription_Block_Adminhtml_Sales_Subscription_View_Renderer_LogMessage',
				));

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

	public function getGridUrl(){
		return $this->getTabUrl();
	}

	/**
	 * ######################## TAB settings #################################
	 */
	public function getTabUrl(){
		return $this->getUrl('*/*/logs', array(
			'_current' => true
		));
	}

	public function getTabClass(){
		return 'ajax';
	}

	public function getTabLabel(){
		return Mage::helper('customweb_subscription')->__('Log');
	}

	public function getTabTitle(){
		return Mage::helper('customweb_subscription')->__('Log');
	}

	public function canShowTab(){
		return true;
	}

	public function isHidden(){
		return false;
	}
}
