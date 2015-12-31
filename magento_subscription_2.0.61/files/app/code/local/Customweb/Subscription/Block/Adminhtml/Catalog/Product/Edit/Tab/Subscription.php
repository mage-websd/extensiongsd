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
class Customweb_Subscription_Block_Adminhtml_Catalog_Product_Edit_Tab_Subscription extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element {

	/**
	 * Element output getter
	 *
	 * @return string
	 */
	public function getElementHtml(){
		$output = '';
		
		$subscriptionElement = $this->_element;
		$block = Mage::app()->getLayout()->createBlock('customweb_subscription/adminhtml_catalog_product_edit_tab_form', 
				'customweb_subscription_adminhtml_edit_form')->setParentElement($subscriptionElement)->setProductEntity(
				Mage::registry('current_product'));
		$output .= $block->toHtml();
		
		$dependencies = Mage::app()->getLayout()->createBlock('adminhtml/widget_form_element_dependence', 
				'customweb_subscription_adminhtml_edit_form_dependence')->addFieldMap('is_subscription', 'product[is_subscription]')->addFieldMap(
				$subscriptionElement->getHtmlId(), $subscriptionElement->getName())->addFieldDependence($subscriptionElement->getName(), 
				'product[is_subscription]', '1')->addConfigOptions(array(
			'levels_up' => 2 
		));
		$output .= $dependencies->toHtml();
		
		$dependencies = Mage::app()->getLayout()->createBlock('adminhtml/widget_form_element_dependence', 
				'customweb_subscription_adminhtml_edit_form_dependence')->addFieldMap('subscription_infosshipping_amount_type', 
				'product[subscription_infos][shipping_amount_type]')->addFieldMap('subscription_infosshipping_amount', 
				'product[subscription_infos][shipping_amount]')->addFieldDependence('product[subscription_infos][shipping_amount]', 
				'product[subscription_infos][shipping_amount_type]', 'fixed');
		$output .= $dependencies->toHtml();
		
		return $output;
	}
}