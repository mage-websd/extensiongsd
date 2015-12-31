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
class Customweb_Subscription_Block_Adminhtml_System_Config_CartPlans extends Customweb_Subscription_Block_Adminhtml_System_Config_TableForm {

	protected function _getPrefix(){
		return 'cart-plans-';
	}

	protected function _getTableTemplate(){
		return 'customweb/subscription/system/config/cartplans.phtml';
	}

	protected function _getRowTemplate(){
		return 'customweb/subscription/system/config/cartplans/row.phtml';
	}

	protected function _getTableBlock(){
		$block = parent::_getTableBlock();
		$block->setPeriodUnitOptions($this->_getPeriodUnitOptions(Mage::helper('adminhtml')->__('-- Please Select --')));
		return $block;
	}

	protected function _getTemplateRowData(){
		$template = Mage::getModel('customweb_subscription/cartPlan');
		$template->setDescription('#{description}');
		$template->setPeriodUnit('#{period_unit}');
		$template->setPeriodFrequency('#{period_frequency}');
		$template->setPeriodMaxCycles('#{period_max_cycles}');
		$template->setInitAmount('#{init_amount}');
		$template->setCancelPeriod('#{cancel_period}');
		$template->setCanCustomerSuspend('#{can_customer_suspend}');
		$template->setSortOrder('#{sort_order}');
		
		return $template;
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
}