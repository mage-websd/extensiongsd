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
class Customweb_Subscription_Block_Adminhtml_System_Config_PaymentMethod extends Mage_Adminhtml_Block_System_Config_Form_Field {
	protected $_paymentMethods = array();

	protected function _getPaymentMethods(){
		if (empty($_paymentMethods)) {
			$payments = Mage::helper('customweb_subscription/payment')->getPaymentMethods();
			$methods = array();
			foreach (Mage::getSingleton('adminhtml/config')->getSections('payment')->payment->groups->asArray() as $paymentCode => $paymentConfig) {
				if (isset($payments[$paymentCode]) && isset($paymentConfig['label'])) {
					$methods[$paymentCode] = array(
						'label' => $paymentConfig['label'],
						'value' => $paymentCode,
						'model' => $payments[$paymentCode] 
					);
				}
			}
			$this->_paymentMethods = $methods;
		}
		
		return $this->_paymentMethods;
	}

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		
		return $this->_getMethodsSelectHtml('payment_method_select');
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

	protected function _getMethodsSelectHtml($id){
		$html = '<fieldset style="padding: 0;"><div style="max-height: 280px; overflow: auto;"><div style="padding: 10px 15px;">';
		$paymentMethods = $this->_getPaymentMethods();
		if (count($paymentMethods) == 0) {
			$html = Mage::helper('customweb_subscription')->__('There are no applicable payment methods.');
		}
		else {
			foreach ($paymentMethods as $paymentMethod) {
				$storedPaymentMethod = in_array($paymentMethod['value'], $this->_getValue());
				$checked = !empty($storedPaymentMethod) ? 'checked="checked"' : '';
				$html .= '<div>
					<input type="checkbox" id="' . $id . '-' . $paymentMethod['value'] . '" value="' . $paymentMethod['value'] . '" name="' .
						 $this->getElement()->getName() . '[]" ' . $this->_getDisabled() . ' ' . $checked . ' />
					<label style="display: block; margin-left: 25px; margin-top: -16px;" for="' . $id . '-' . $paymentMethod['value'] . '">' .
						 $paymentMethod['label'] . '</label>
				</div>';
			}
		}
		$html .= '</div></div></fieldset>';
		return $html;
	}
}
