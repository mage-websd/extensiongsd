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
class Customweb_Subscription_Model_Observer {

	/**
	 * Add the subscription's configuration and base product selection tab
	 * to the product form.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareProductEditForm(Varien_Event_Observer $observer){
		$form = $observer->getEvent()->getForm();

		$groups = $form->getElements();
		$fieldset = $groups[0];
		$configurableHelper = $form->getElement('subscription_plan');
		if ($configurableHelper) {
			$fieldset->removeField('subscription_plan');
		}

		$isSubscription = $form->getElement('is_subscription');
		$subscriptionInfos = $form->getElement('subscription_infos');
		if ($isSubscription && $subscriptionInfos) {
			$subscriptionInfos->setRenderer(
					Mage::app()->getLayout()->createBlock('customweb_subscription/adminhtml_catalog_product_edit_tab_subscription'));
		}
	}

	/**
	 * Add informational options to the product in the cart.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onCartProductAddAfter(Varien_Event_Observer $observer){
		$event = $observer->getEvent();
		$item = $event->getQuoteItem();

		if ($item->isSubscription()) {
			$product = Mage::getModel('catalog/product')->load($item->getProductId());

			$additionalOptions = array();
			if ($additionalOption = $item->getOptionByCode('additional_options')) {
				$additionalOptions = (array) unserialize($additionalOption->getValue());
			}

			if ($product->getInitAmount()) {
				$additionalOptions[] = array(
					'label' => $product->getInitAmount() > 0 ? Mage::helper('customweb_subscription')->__('Initial Fee') : Mage::helper(
							'customweb_subscription')->__('Initial Discount'),
					'value' => Mage::helper('core')->currency($product->getInitAmount(), true, false)
				);
			}

			$item->addOption(array(
				'code' => 'additional_options',
				'value' => serialize($additionalOptions)
			));
		}
	}

	/**
	 * When customer logs in during checkout, merge subscription into quote.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onQuoteMergeAfter(Varien_Event_Observer $observer){
		$event = $observer->getEvent();
		$quote = $event->getQuote();
		$source = $event->getSource();

		if ($source->getSubscriptionPlan()) {
			$quote->setSubscriptionPlan($source->getSubscriptionPlan());
		}
	}

	/**
	 * When buying a recurring product, create a subscription.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onOrderPlaceAfter(Varien_Event_Observer $observer){
		$event = $observer->getEvent();
		$order = $event->getOrder();

		if (Mage::registry('customweb_subscription_recurring_order')) {
			return;
		}

		$isSubscription = false;
		$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
		if ($quote != null && $quote->getId() && $quote->getSubscriptionPlan() != null) {
			$isSubscription = true;
			$subscription = Mage::getModel('customweb_subscription/subscription')->importCartPlan($quote->getSubscriptionPlan());
		}

		if (!$isSubscription) {
			foreach ($order->getAllItems() as $item) {
				if ($item->getParentItemId() != null) {
					continue;
				}

				if (is_object($item->getProduct()) && $item->getProduct()->isSubscription()) {
					if ($item->getProduct()->isConfigurable() && $productOption = $item->getProduct()->getCustomOption('simple_product')) {
						if ($optionProductId = $productOption->getProductId()) {
							$product = Mage::getModel('catalog/product')->load($optionProductId);
						}
					}
					else {
						$product = Mage::getModel('catalog/product')->load($item->getProductId());
					}
					$isSubscription = true;
					break;
				}
			}
			if ($isSubscription) {
				$subscription = Mage::getModel('customweb_subscription/subscription')->importProduct($product);
			}
		}

		if ($isSubscription && $subscription) {
			$subscription->importOrder($order);
			$subscription->importOrderItems($order->getAllItems());
			$subscription->submit();
			$subscription->addOrderRelation($order->getId());
		}
	}

	/**
	 * Update the subscription after capturing a payment.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onPaymentPayAfter(Varien_Event_Observer $observer){
		$event = $observer->getEvent();
		$payment = $event->getPayment();

		$subscription = Mage::getModel('customweb_subscription/subscription')->load($payment->getId(), 'payment_id');
		if ($subscription && $subscription->isUnknown()) {
			$subscription->activate();
			return;
		}

		$subscriptionId = Mage::getModel('customweb_subscription/order')->load($payment->getOrder()->getId(), 'order_id')->getSubscriptionId();
		$subscription = Mage::getModel('customweb_subscription/subscription')->load($subscriptionId);
		if ($subscription && ($subscription->isPaid() || $subscription->isPending() || $subscription->isAuthorized())) {
			$subscription->afterCapture($payment->getOrder());
			return;
		}
	}

	/**
	 * Cancel the subscription after the initial payment is canceled.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onPaymentCancelAfter(Varien_Event_Observer $observer){
		$event = $observer->getEvent();
		$payment = $event->getPayment();

		$subscription = Mage::getModel('customweb_subscription/subscription')->load($payment->getId(), 'payment_id');
		if ($subscription->getId() && !$subscription->isCanceled()) {
			$subscription->cancel();
		}
	}

	/**
	 * Disable payment method that cannot be used with recurring products.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function paymentMethodIsActive(Varien_Event_Observer $observer){
		$checkResult = $observer->getEvent()->getResult();
		$method = $observer->getEvent()->getMethodInstance();

		if ($checkResult->isAvailable) {
			if (Mage::helper('customweb_subscription/cart')->isQuoteRecurring() &&
					 !Mage::helper('customweb_subscription/payment')->isPaymentMethodEnabled($method, Mage::app()->getStore()->getId())) {
				$checkResult->isAvailable = false;
			}
		}
	}

	public function checkoutAllowGuest(Varien_Event_Observer $observer){
		$quote = $observer->getEvent()->getQuote();
		$result = $observer->getEvent()->getResult();
		if (!Mage::getStoreConfig('customweb_subscription/checkout/allow_guest') && $quote->isSubscription()) {
			$result->setIsAllowed(false);
		}
	}

	/**
	 * Change transaction context to being able to override some values.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onCreateTransactionContext(Varien_Event_Observer $observer){
		$order = $observer->getEvent()->getOrder();
		$result = $observer->getEvent()->getResult();

		if (Mage::helper('customweb_subscription/cart')->isQuoteRecurring()) {
			$result->transactionContext = new Customweb_Subscription_Model_TransactionContext($result->transactionContext, true);
		}

		if (Mage::registry('customweb_subscription_recurring_order')) {
			$subscription = Mage::getModel('customweb_subscription/subscription')->load(Mage::getSingleton('core/session')->getSubscriptionId());
			$result->transactionContext = new Customweb_Subscription_Model_RecurringTransactionContext($result->transactionContext, false,
					$subscription);
		}
	}

	/**
	 * Update subscription after the payment is processed.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function onAfterProcess(Varien_Event_Observer $observer){
		$order = $observer->getEvent()->getOrder();
		$parameters = $observer->getEvent()->getParameters();

		if (isset($parameters['cwsubscriptionid'])) {
			$subscription = Mage::getModel('customweb_subscription/subscription')->load($parameters['cwsubscriptionid']);
			$payment = new Customweb_Subscription_Model_Method($order->getPayment()->getMethodInstance(), $subscription);
			$payment->afterProcess($order, $parameters);
		}
	}

	public function onCollectInvoiceItems(Varien_Event_Observer $observer){
		$invoice = $observer->getEvent()->getInvoice();
		$result = $observer->getEvent()->getResult();

		$initAmount = $invoice->getOrder()->getSubscriptionInitAmount();
		if ($initAmount > 0) {
			$result->items[] = $this->getInitAmountItem($initAmount);
		}
	}

	public function onCollectOrderItems(Varien_Event_Observer $observer){
		$order = $observer->getEvent()->getOrder();
		$result = $observer->getEvent()->getResult();

		$initAmount = $order->getSubscriptionInitAmount();
		if ($initAmount > 0) {
			$result->items[] = $this->getInitAmountItem($initAmount);
		}
	}

	public function onCollectQuoteItems(Varien_Event_Observer $observer){
		$quote = $observer->getEvent()->getQuote();
		$result = $observer->getEvent()->getResult();

		$initAmount = $quote->getSubscriptionInitAmount();
		if ($initAmount > 0) {
			$result->items[] = $this->getInitAmountItem($initAmount);
		}
	}

	public function onAfterSaveTransaction(Varien_Event_Observer $observer){
		$transaction = $observer->getEvent()->getTransaction();

		$subscription = Mage::helper('customweb_subscription')->getSubscriptionByOrder($transaction->getOrder());
		if ($subscription != null) {
			$subscription->updateStatusByTransaction($transaction);
		}
	}

	private function getInitAmountItem($initAmount){
		return array(
			'sku' => 'subscription_init_amount',
			'name' => $initAmount > 0 ? Mage::helper('customweb_subscription')->__('Initial Subscription Fee') : Mage::helper(
					'customweb_subscription')->__('Initial Subscription Discount'),
			'taxRate' => 0,
			'amountIncludingTax' => abs($initAmount),
			'quantity' => 1,
			'type' => $initAmount > 0 ? 'fee' : 'discount'
		);
	}
}