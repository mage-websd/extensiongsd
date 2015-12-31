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
class Customweb_Subscription_Block_Adminhtml_System_Config_Reminders extends Customweb_Subscription_Block_Adminhtml_System_Config_TableForm {

	protected function _getPrefix(){
		return 'reminders-';
	}

	protected function _getTableTemplate(){
		return 'customweb/subscription/system/config/reminders.phtml';
	}

	protected function _getRowTemplate(){
		return 'customweb/subscription/system/config/reminders/row.phtml';
	}

	protected function _getTableBlock(){
		$block = parent::_getTableBlock();
		$block->setUnitOptions($this->_getUnitOptions(Mage::helper('adminhtml')->__('-- Please Select --')));
		return $block;
	}

	protected function _getTemplateRowData(){
		$template = Mage::getModel('customweb_subscription/reminder');
		$template->setCount('#{count}');
		$template->setUnit('#{unit}');
		$template->setEmailTemplate('#{email_template}');
		return $template;
	}

	protected function _getRow($data, $index){
		if ($data->getEmailTemplateLabel() == null) {
			$data->setEmailTemplateLabel(
					$this->_getEmailTemplateLabel('customweb_subscription_email_template_reminder', $data->getEmailTemplate()));
		}
		return parent::_getRow($data, $index);
	}

	protected function _getEmailTemplateLabel($path, $value){
		$options = Mage::getModel('adminhtml/system_config_source_email_template')->setPath($path)->toOptionArray();
		foreach ($options as $option) {
			if ($option['value'] == $value) {
				return $option['label'];
			}
		}
	}

	/**
	 * Getter for period unit options with "Please Select" label
	 *
	 * @return array
	 */
	protected function _getUnitOptions($emptyLabel){
		$options = array();
		$options[''] = $emptyLabel;
		foreach (Customweb_Subscription_Model_PeriodUnit::values() as $periodUnit) {
			$options[$periodUnit->getName()] = $periodUnit->getLabel();
		}
		return $options;
	}
}