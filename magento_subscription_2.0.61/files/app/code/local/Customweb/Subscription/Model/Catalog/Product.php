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

/**
 * Override the product model to add custom behaviour.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Catalog_Product extends Mage_Catalog_Model_Product {
	
	/**
	 * This product's subscription plan instance.
	 *
	 * @var Customweb_Subscription_Model_Plan
	 */
	protected $_plan = null;

	/**
	 * Check whether this is a subscription.
	 *
	 * @return boolean
	 */
	public function isSubscription(){
		if ($this->isConfigurable() && $productOption = $this->getCustomOption('simple_product')) {
			if ($optionProductId = $productOption->getProductId()) {
				return Mage::getModel('catalog/product')->load($optionProductId)->isSubscription();
			}
		}
		return $this->getData('is_subscription') == 1;
	}

	/**
	 * Return this product's subscription information.
	 *
	 * @return array
	 */
	public function getSubscriptionInfos(){
		if ($this->isConfigurable() && $productOption = $this->getCustomOption('simple_product')) {
			if ($optionProductId = $productOption->getProductId()) {
				return Mage::getModel('catalog/product')->load($optionProductId)->getSubscriptionInfos();
			}
		}
		
		return $this->getData('subscription_infos');
	}

	/**
	 * Return this product's subscription plan instance.
	 *
	 * @return Customweb_Subscription_Model_Plan
	 */
	public function getPlan(){
		if ($this->isSubscription()) {
			if ($this->_plan == null) {
				$subscriptionInfos = $this->getSubscriptionInfos();
				if (!is_array($subscriptionInfos)) {
					$subscriptionInfos = unserialize($subscriptionInfos);
				}
				$this->_plan = Mage::getModel('customweb_subscription/plan')->fromArray($subscriptionInfos);
			}
			return $this->_plan;
		}
	}

	/**
	 * Return the subscription's initial amount.
	 *
	 * @return float
	 */
	public function getInitAmount(){
		$subscriptionInfos = $this->getSubscriptionInfos();
		if (!is_array($subscriptionInfos)) {
			$subscriptionInfos = unserialize($subscriptionInfos);
		}
		if (isset($subscriptionInfos['init_amount'])) {
			return $subscriptionInfos['init_amount'];
		}
		return 0;
	}

	/**
	 * Return the number of days the subscription's
	 * cancelation period lasts.
	 *
	 * @return int
	 */
	public function getCancelPeriod(){
		$subscriptionInfos = $this->getSubscriptionInfos();
		if (!is_array($subscriptionInfos)) {
			$subscriptionInfos = unserialize($subscriptionInfos);
		}
		if (isset($subscriptionInfos['cancel_period'])) {
			return $subscriptionInfos['cancel_period'];
		}
	}

	/**
	 * Return the subscription's description.
	 *
	 * @return string
	 */
	public function getPlanDescription(){
		$subscriptionInfos = $this->getSubscriptionInfos();
		if (!is_array($subscriptionInfos)) {
			$subscriptionInfos = unserialize($subscriptionInfos);
		}
		if (isset($subscriptionInfos['description'])) {
			return $subscriptionInfos['description'];
		}
	}

	/**
	 * When saving the product, assign a subscription plan attribute option.
	 * This is used with configurable products.
	 */
	protected function _beforeSave(){
		$allowProductTypes = array();
		foreach (Mage::helper('catalog/product_configuration')->getConfigurableAllowedTypes() as $type) {
			$allowProductTypes[] = $type->getName();
		}
		if (in_array($this->getTypeId(), $allowProductTypes)) {
			$helperAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', 'subscription_plan');
			
			if (!$this->isSubscription()) {
				$optionValue = 'None';
			}
			else {
				$optionValue = $this->getPlanDescription();
			}
			
			if (!($optionId = $this->getAttributeOptionId($helperAttribute, $optionValue))) {
				$option['attribute_id'] = $helperAttribute->getAttributeId();
				$option['value']['option_name'][0] = $optionValue;
				
				$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
				$setup->addAttributeOption($option);
				
				$optionId = $this->getAttributeOptionId($helperAttribute, $optionValue);
			}
			
			$this->setSubscriptionPlan($optionId);
		}
		
		parent::_beforeSave();
	}

	/**
	 * Return the value of an option.
	 *
	 * @param string $attribute
	 * @param string $value
	 * @return mixed|boolean
	 */
	protected function getAttributeOptionId($attribute, $value){
		$options = Mage::getModel('eav/entity_attribute_source_table')->setAttribute($attribute)->getAllOptions(false);
		
		foreach ($options as $option) {
			if ($option['label'] == $value) {
				return $option['value'];
			}
		}
		
		return false;
	}
}