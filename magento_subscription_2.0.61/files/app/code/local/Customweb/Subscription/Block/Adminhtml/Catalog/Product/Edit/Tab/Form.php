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
class Customweb_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Form extends Mage_Adminhtml_Block_Catalog_Form {
	/**
	 * Reference to the parent element (optional)
	 *
	 * @var Varien_Data_Form_Element_Abstract
	 */
	protected $_parentElement = null;
	
	/**
	 * Whether the form contents can be editable
	 *
	 * @var bool
	 */
	protected $_isReadOnly = false;
	
	/**
	 * Recurring subscription instance used for getting period options
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	protected $_subscription = null;
	
	/**
	 *
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_product = null;

	/**
	 * Setter for parent element
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 */
	public function setParentElement(Varien_Data_Form_Element_Abstract $element){
		$this->_parentElement = $element;
		return $this;
	}

	/**
	 * Setter for current product
	 *
	 * @param Mage_Catalog_Model_Product $product
	 */
	public function setProductEntity(Mage_Catalog_Model_Product $product){
		$this->_product = $product;
		return $this;
	}

	/**
	 * Instantiate a recurring payment subscription to use it as a helper
	 */
	protected function _construct(){
		$this->_subscription = Mage::getSingleton('customweb_subscription/subscription');
		return parent::_construct();
	}

	/**
	 * Prepare and render the form
	 *
	 * @return string
	 */
	protected function _toHtml(){
		$form = $this->_prepareForm();
		if ($this->_product && $this->_product->getSubscriptionInfos()) {
			$form->setValues($this->_product->getSubscriptionInfos());
		}
		return $form->toHtml();
	}

	/**
	 * Instantiate form and fields
	 *
	 * @return Varien_Data_Form
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setFieldsetRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset'));
		$form->setFieldsetElementRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element'));
		
		$form->setDataObject($this->_parentElement->getForm()->getDataObject());
		
		/**
		 * if there is a parent element defined, it will be replaced by a hidden element with the same name
		 * and overriden by the form elements
		 * It is needed to maintain HTML consistency of the parent element's form
		 */
		if ($this->_parentElement) {
			$form->setHtmlIdPrefix($this->_parentElement->getHtmlId())->setFieldNameSuffix($this->_parentElement->getName());
			$form->addField('', 'hidden', array(
				'name' => '' 
			));
		}
		
		$noYes = array(
			Mage::helper('adminhtml')->__('No'),
			Mage::helper('adminhtml')->__('Yes') 
		);
		
		// initial amount
		$schedule = $form->addFieldset('initamount_fieldset', 
				array(
					'legend' => Mage::helper('customweb_subscription')->__('Initial Fee/Discount'),
					'disabled' => $this->_isReadOnly 
				));
		$this->_addField($schedule, 'init_amount');
		
		// schedule
		$schedule = $form->addFieldset('schedule_fieldset', 
				array(
					'legend' => Mage::helper('customweb_subscription')->__('Schedule'),
					'disabled' => $this->_isReadOnly 
				));
		$this->_addField($schedule, 'description');
		$this->_addField($schedule, 'period_unit', 
				array(
					'options' => $this->_getPeriodUnitOptions(Mage::helper('adminhtml')->__('-- Please Select --')) 
				), 'select');
		$this->_addField($schedule, 'period_frequency');
		$this->_addField($schedule, 'period_max_cycles');
		
		$this->_addField($schedule, 'cancel_period');
		
		$this->_addField($schedule, 'can_customer_suspend', 
				array(
					'options' => array(
						1 => Mage::helper('adminhtml')->__('Yes'),
						0 => Mage::helper('adminhtml')->__('No') 
					) 
				), 'select');
		
		// shipping
		$shipping = $form->addFieldset('shipping_fieldset', 
				array(
					'legend' => Mage::helper('customweb_subscription')->__('Shipping'),
					'disabled' => $this->_isReadOnly 
				));
		$this->_addElementTypes($shipping);
		$this->_addField($shipping, 'shipping_amount_type', 
				array(
					'options' => array(
						'fixed' => Mage::helper('customweb_subscription')->__('Fixed Shipping'),
						'calculated' => Mage::helper('customweb_subscription')->__('Equals initial order') 
					) 
				), 'select');
		$this->_addField($shipping, 'shipping_amount', array(), 'price');
		
		return $form;
	}

	/**
	 * Add a field to the form or fieldset
	 * Form and fieldset have same abstract
	 *
	 * @param Varien_Data_Form|Varien_Data_Form_Element_Fieldset $formOrFieldset
	 * @param string $elementName
	 * @param array $options
	 * @param string $type
	 * @return Varien_Data_Form_Element_Abstract
	 */
	protected function _addField($formOrFieldset, $elementName, $options = array(), $type = 'text'){
		$options = array_merge($options, 
				array(
					'name' => $elementName,
					'label' => $this->getFieldLabel($elementName),
					'note' => $this->getFieldComment($elementName),
					'disabled' => $this->_isReadOnly 
				));
		if (in_array($elementName, array(
			'period_unit',
			'period_frequency',
			'description' 
		))) {
			$options['required'] = true;
		}
		$attribute = new Varien_Object();
		$attribute->setAttributeCode($elementName);
		return $formOrFieldset->addField($elementName, $type, $options)->setEntityAttribute($attribute);
	}

	/**
	 * Getter for period unit options with "Please Select" label
	 *
	 * @return array
	 */
	protected function _getPeriodUnitOptions($emptyLabel){
		$options = array();
		$options[''] = $emptyLabel;
		foreach (Customweb_Subscription_Model_PeriodUnit::values() as $periodUnit) {
			$options[$periodUnit->getName()] = $periodUnit->getLabel();
		}
		return $options;
	}

	/**
	 * Set readonly flag
	 *
	 * @param boolean $isReadonly
	 * @return Customweb_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Form
	 */
	public function setIsReadonly($isReadonly){
		$this->_isReadOnly = $isReadonly;
		return $this;
	}

	/**
	 * Get readonly flag
	 *
	 * @return boolean
	 */
	public function getIsReadonly(){
		return $this->_isReadOnly;
	}

	/**
	 * Retrieve additional element types
	 *
	 * @return array
	 */
	protected function _getAdditionalElementTypes(){
		return array(
			'price' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
			'boolean' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean') 
		);
	}

	/**
	 * Getter for field label
	 *
	 * @param string $field
	 * @return string|null
	 */
	public function getFieldLabel($field){
		$helper = Mage::helper('customweb_subscription');
		switch ($field) {
			case 'description':
				return $helper->__('Description');
			case 'period_unit':
				return $helper->__('Billing Period Unit');
			case 'period_frequency':
				return $helper->__('Billing Frequency');
			case 'period_max_cycles':
				return $helper->__('Maximum Billing Cycles');
			case 'cancel_period':
				return $helper->__('Period Of Notice');
			case 'can_customer_suspend':
				return $helper->__('Can Customer Suspend');
			case 'shipping_amount_type':
				return $helper->__('Shipping Amount Type');
			case 'shipping_amount':
				return $helper->__('Shipping Amount');
			case 'init_amount':
				return $helper->__('Initial Fee/Discount');
		}
	}

	/**
	 * Getter for field comments
	 *
	 * @param string $field
	 * @return string|null
	 */
	public function getFieldComment($field){
		$helper = Mage::helper('customweb_subscription');
		switch ($field) {
			case 'description':
				return $helper->__('Describe the subscription and schedule. The value of this field is used for configurable product options.');
			case 'period_unit':
				return $helper->__('Unit for billing during the subscription period.');
			case 'period_frequency':
				return $helper->__('Number of billing periods that make up one billing cycle.');
			case 'period_max_cycles':
				return $helper->__('The subscriptions ends automatically after the above entered number of billing cycles.');
			case 'cancel_period':
				return $helper->__('The number of billing cycles the subscription will keep running after the cancelation is requested.');
			case 'can_customer_suspend':
				return $helper->__('Is the customer allowed to suspend the subscription?');
			case 'shipping_amount_type':
				return $helper->__('Whether to use a fixed shipping amount or calculate it dynamically.');
			case 'shipping_amount':
				return $helper->__('In case you selected a fixed shipping type, enter the shipping costs above.');
			case 'init_amount':
				return $helper->__('Define a fee or a discount (negative value) that is added to or subtracted from the initial payment.');
		}
	}
}
