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
class Customweb_Subscription_Block_Adminhtml_System_Config_ProductFilter extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		
		return $this->_getChooserHtml();
	}

	protected function _getDisabled(){
		return $this->getElement()->getDisabled() ? ' disabled' : '';
	}

	protected function _getValue(){
		$value = $this->getElement()->getData('value');
		if (!is_array($value)) {
			return explode(',', $value);
		}
		return $value;
	}

	protected function _getChooserHtml(){
		$chooser = $this->getLayout()->createBlock('customweb_subscription/adminhtml_widget_chooser')->setName(
				Mage::helper('core')->uniqHash('customweb_subscription_product_filter_'))->setUseMassaction(true)->setSelectedProducts(
				$this->_getValue());
		
		$serializer = $this->getLayout()->createBlock('customweb_subscription/adminhtml_widget_serializer');
		$serializer->initSerializerBlock($chooser, 'getSelectedProducts', 'groups[cart][fields][product_filter][value]', 'selected_products');
		
		return '<div style="width:700px;">' . $chooser->toHtml() . $serializer->toHtml() . '</div>';
	}
}
