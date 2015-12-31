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
abstract class Customweb_Subscription_Block_Adminhtml_System_Config_TableForm extends Mage_Adminhtml_Block_System_Config_Form_Field {

	abstract protected function _getPrefix();

	abstract protected function _getTableTemplate();

	abstract protected function _getRowTemplate();

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		
		return $this->_getTableBlock()->toHtml();
	}

	protected function _getTableBlock(){
		return $this->getLayout()->createBlock('adminhtml/abstract')->setTemplate($this->_getTableTemplate())->setDisabled($this->_getDisabled())->setAddRowButtonHtml(
				$this->_getAddRowButtonHtml())->setRows($this->_getRows())->setRowTemplate($this->_getTemplateRow());
	}

	abstract protected function _getTemplateRowData();

	protected function _getTemplateRow(){
		return $this->_getRow($this->_getTemplateRowData(), '#{index}');
	}

	protected function _getRows(){
		$index = 0;
		$rows = '';
		if ($this->_getValue()) {
			foreach ($this->_getValue() as $value) {
				if ($value && $value != '#{index}') {
					$rows .= $this->_getRow($value, ++$index);
				}
			}
		}
		
		return $rows;
	}

	protected function _getRow($data, $index){
		return $this->getLayout()->createBlock('adminhtml/abstract')->setTemplate($this->_getRowTemplate())->setIndex($index)->setRowData($data)->setElement(
				$this->getElement())->toHtml();
	}

	protected function _getDisabled(){
		return $this->getElement()->getDisabled() ? ' disabled' : '';
	}

	protected function _getValue(){
		return $this->getElement()->getData('value');
	}

	protected function _getAddRowButtonHtml(){
		return $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')->setClass('add ' . $this->_getDisabled())->setLabel(
				$this->__('Add'))->setId($this->_getPrefix() . 'btn-add')->setDisabled($this->_getDisabled())->toHtml();
	}
}