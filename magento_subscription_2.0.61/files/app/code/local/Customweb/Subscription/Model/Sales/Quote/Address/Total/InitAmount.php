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
class Customweb_Subscription_Model_Sales_Quote_Address_Total_InitAmount extends Mage_Sales_Model_Quote_Address_Total_Abstract {

	/**
	 *
	 * @var string
	 */
	protected $_code = 'subscription_init_amount';

	public function collect(Mage_Sales_Model_Quote_Address $address){
		parent::collect($address);

		$address->setBaseSubscriptionInitAmount(0);
		$address->setSubscriptionInitAmount(0);

		$this->_setAddress($address);
		$this->_setAmount(0);
		$this->_setBaseAmount(0);

		$items = $this->_getAddressItems($address);
		if (!count($items)) {
			return $this;
		}

		$quote = $address->getQuote();

		$baseInitAmount = $this->getInitAmount($quote);
		$initAmount = $address->getQuote()->getStore()->convertPrice($baseInitAmount, false);
		if (!Mage::registry('customweb_subscription_recurring_order') && $baseInitAmount) {
			$address->setBaseSubscriptionInitAmount($baseInitAmount);
			$address->setSubscriptionInitAmount($initAmount);

			$quote->setBaseSubscriptionInitAmount($baseInitAmount);
			$quote->setSubscriptionInitAmount($initAmount);

			$address->setGrandTotal($address->getGrandTotal() + $initAmount);
			$address->setBaseGrandTotal($address->getBaseGrandTotal() + $initAmount);

			$this->_calculateTax($address);
		}
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address){
		$items = $this->_getAddressItems($address);
		if (!count($items)) {
			return $this;
		}

		$quote = $address->getQuote();

		if (!Mage::registry('customweb_subscription_recurring_order') && $quote->isSubscription() && $initAmount = $this->getInitAmount($quote)) {
			$address->addTotal(
					array(
						'code' => $this->getCode(),
						'title' => $initAmount > 0 ? Mage::helper('customweb_subscription')->__('Initial Subscription Fee') : Mage::helper(
								'customweb_subscription')->__('Initial Subscription Discount'),
						'value' => $initAmount
					));
		}
		return $this;
	}

	protected function getInitAmount(Mage_Sales_Model_Quote $quote){
		$initAmount = 0;
		if ($quote->getSubscriptionPlan() != null) {
			$initAmount = (float) $quote->getSubscriptionPlan()->getInitAmount();
		}
		else {
			foreach ($quote->getAllItems() as $item) {
				$product = $item->getProduct();
				$product->load($product->getId());
				if ($item->isSubscription() && $product->getInitAmount()) {
					$initAmount += $product->getInitAmount() * $item->getQty();
				}
			}
		}
		return $initAmount;
	}

	protected function _calculateTax(Mage_Sales_Model_Quote_Address $address)
	{
		$calculator = Mage::getSingleton('tax/calculation');
		$calculator->setCustomer($address->getQuote()->getCustomer());

		$inclTax = Mage::getSingleton('tax/config')->priceIncludesTax($address->getQuote()->getStore());

		$taxRateRequest = $calculator->getRateRequest(
				$address,
				$address->getQuote()->getBillingAddress(),
				$address->getQuote()->getCustomerTaxClassId(),
				$address->getQuote()->getStore()
		);

		$taxRateRequest->setProductClassId(Mage::getStoreConfig('tax/classes/customweb_subscription_initamount_tax_class', $address->getQuote()->getStore()));

		$rate = $calculator->getRate($taxRateRequest);

		if($rate > 0.0) {
			$baseTax = $calculator->calcTaxAmount($address->getBaseSubscriptionInitAmount(), $rate, $inclTax, true);
			$tax = $address->getQuote()->getStore()->convertPrice($baseTax, false);

			$address->addTotalAmount('tax', $tax);
			$address->addBaseTotalAmount('tax', $baseTax);

			$rates = array();
			foreach ($address->getAppliedTaxes() as $rate) {
				$rate['amount'] = $rate['amount'] + $tax;
				$rate['base_amount'] = $rate['base_amount'] + $baseTax;
				$rates[] = $rate;
			}

			$address->setAppliedTaxes($rates);

			if($inclTax) {
				$address->setGrandTotal($address->getGrandTotal() - $tax);
				$address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseTax);
			}
		}

	}
}