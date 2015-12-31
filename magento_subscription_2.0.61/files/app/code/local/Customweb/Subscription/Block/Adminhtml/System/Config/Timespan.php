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
class Customweb_Subscription_Block_Adminhtml_System_Config_Timespan extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		$value = $this->_getValue();
		$html = '<input style="width: 50px;" id="' . $this->getElement()->getHtmlId() . '-count" name="' . $this->getElement()->getName() .
				 '[count]" value="' . (isset($value['count']) ? $value['count'] : '') . '" class=" input-text" type="text" ' . $this->_getDisabled() .
				 '>';
		$html .= '<select style="width: 234px; margin-left: 10px; id="' . $this->getElement()->getHtmlId() . '-unit" name="' .
				 $this->getElement()->getName() . '[unit]" ' . $this->_getDisabled() . '>';
		foreach (Customweb_Subscription_Model_PeriodUnit::values() as $unit) {
			$html .= '<option value="' . $unit->getName() . '" ' .
					 ((isset($value['unit']) && $value['unit'] == $unit->getName()) ? 'selected="selected"' : '') . '>' . $unit->getLabel() .
					 '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	protected function _getDisabled(){
		return $this->getElement()->getDisabled() ? ' disabled' : '';
	}

	protected function _getValue(){
		$value = $this->getElement()->getData('value');
		if (!is_array($value)) {
			return array();
		}
		return $value;
	}
}
