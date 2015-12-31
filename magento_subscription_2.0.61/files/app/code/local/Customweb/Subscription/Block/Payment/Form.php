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
class Customweb_Subscription_Block_Payment_Form extends Mage_Core_Block_Template {

	/**
	 * @var Customweb_Subscription_Model_Method
	 */
	protected $_paymentMethod = null;

	public function getSubscription(){
		return Mage::registry('current_subscription');
	}

	public function getMethod(){
		if ($this->_paymentMethod == null) {
			$this->_paymentMethod = new Customweb_Subscription_Model_Method($this->getSubscription()->getMethodInstance(), $this->getSubscription());
		}
		return $this->_paymentMethod;
	}

	public function getMethodCode(){
		return $this->getMethod()->getCode();
	}

	public function getFormActionUrl(){
		$adapter = $this->getMethod()->getAuthorizationAdapter(false);

		if ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter) {
			return Mage::getUrl('subscription/payment/iframe',
					array(
						'_secure' => true,
						'subscription_id' => $this->getSubscription()->getId()
					));
		}
		if ($adapter instanceof Customweb_Payment_Authorization_Widget_IAdapter) {
			return Mage::getUrl('subscription/payment/widget',
					array(
						'_secure' => true,
						'subscription_id' => $this->getSubscription()->getId()
					));
		}
		if ($adapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter) {
			return Mage::getUrl('subscription/payment/redirect',
					array(
						'_secure' => true,
						'subscription_id' => $this->getSubscription()->getId()
					));
		}

		return Mage::getUrl('subscription/payment/createOrder',
				array(
					'_secure' => true,
					'subscription_id' => $this->getSubscription()->getId()
				));
	}

	public function getFormFields(){
		return $this->getMethod()->generateVisibleFormFields(array(
			'alias_id' => 'new'
		));
	}

	public function getCancelUrl(){
		return Mage::getUrl('*/*/cancel', array(
			'_current' => true
		));
	}

	public function getProcessUrl(){
		return Mage::getUrl('subscription/payment/process', array(
			'_secure' => true
		));
	}

	public function getJavascriptUrl(){
		return Mage::getUrl('subscription/payment/ajax', array(
			'_secure' => true
		));
	}

	public function getHiddenFieldsUrl(){
		return Mage::getUrl('subscription/payment/getHiddenFields', array(
			'_secure' => true
		));
	}

	public function getVisibleFieldsUrl(){
		return Mage::getUrl('subscription/payment/getVisibleFields', array(
			'_secure' => true
		));
	}

	public function getAuthorizationMethod(){
		$adapter = $this->getMethod()->getAuthorizationAdapter(false);

		$className = get_class($adapter);
		$nameTokens = explode('_', $className);

		return strtolower($nameTokens[count($nameTokens) - 2]);
	}

	public function getMethodDescription(){
		return $this->getMethod()->getPaymentMethodConfigurationValue('description', Mage::app()->getLocale()->getLocaleCode());
	}

	public function getAliasSelect(){
		$payment = $this->getMethod();
		$result = '';

		if ($payment->getHelper()->getConfigurationValue('alias_manager') != 'inactive') {
			$aliasList = $payment->loadAliasForCustomer();

			if (count($aliasList)) {
				$alias = array(
					'new' => ''
				);

				foreach ($aliasList as $key => $value) {
					$alias[$key] = $value;
				}

				// The onchange even listener is added here, because there seems to be a bug with prototype's observe
				// on select fields. ::[License_Identifier]::
				$selectControl = new Customweb_Form_Control_Select('alias', $alias, 'new');
				$aliasElement = new Customweb_Form_Element($payment->getHelper()->__('Saved cards: '), $selectControl,
						$payment->getHelper()->__('You may choose one of the cards you paid before on this site.'));
				$aliasElement->setRequired(false);

				$result = $payment->getFormRenderer()->renderElements(array(
					0 => $aliasElement
				));
			}
		}

		return $result;
	}
}