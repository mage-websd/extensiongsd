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

/**
 * Override the order model to add custom behaviour.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Sales_Order extends Mage_Sales_Model_Order {

	/**
	 * Email template config paths
	 */
	const XML_PATH_EMAIL_SUBSCRIPTION_TEMPLATE = 'customweb_subscription/email/template_order';
	const XML_PATH_EMAIL_SUBSCRIPTION_GUEST_TEMPLATE = 'customweb_subscription/email/template_order_guest';

	const XML_PATH_EMAIL_SUBSCRIPTION_RECURRING_TEMPLATE = 'customweb_subscription/email/template_recurring_order';
	const XML_PATH_EMAIL_SUBSCRIPTION_RECURRING_GUEST_TEMPLATE = 'customweb_subscription/email/template_recurring_order_guest';

	/**
	 * Queue email with new order data
	 *
	 * @param bool $forceMode if true then email will be sent regardless of the fact that it was already sent previously
	 *
	 * @return Mage_Sales_Model_Order
	 * @throws Exception
	 */
	public function queueNewOrderEmail($forceMode = false){
		if (Mage::helper('customweb_subscription/cart')->isOrderSubscription($this)
				|| Mage::registry('customweb_subscription_recurring_order') === true) {
			$this->sendNewSubscriptionOrderEmail();
		}
		else {
			parent::queueNewOrderEmail($forceMode);
		}
	}

	/**
	 * Send email with order data
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function sendNewOrderEmail(){
		if (Mage::helper('customweb_subscription/cart')->isOrderSubscription($this)
				|| Mage::registry('customweb_subscription_recurring_order') === true) {
			$this->sendNewSubscriptionOrderEmail();
		}
		else {
			parent::sendNewOrderEmail();
		}
	}

	/**
	 * Send email with order data
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function sendNewSubscriptionOrderEmail(){
		if (!Mage::helper('customweb_subscription/cart')->isOrderSubscription($this)
				&& Mage::registry('customweb_subscription_recurring_order') !== true) {
			$this->sendNewOrderEmail();
			return;
		}

		$storeId = $this->getStore()->getId();

		if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
			return $this;
		}
		// Get the destination email addresses to send copies to
		$copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
		$copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

		// Start store emulation process
		$appEmulation = Mage::getSingleton('core/app_emulation');
		$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

		try {
			// Retrieve specified view block from appropriate design package (depends on emulated store)
			$paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())->setIsSecureMode(true);
			$paymentBlock->getMethod()->setStore($storeId);
			$paymentBlockHtml = $paymentBlock->toHtml();
		}
		catch (Exception $exception) {
			// Stop store emulation process
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			throw $exception;
		}

		// Stop store emulation process
		$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

		// Retrieve corresponding email template id and customer name
		if ($this->getCustomerIsGuest()) {
			if (Mage::registry('customweb_subscription_recurring_order') === true) {
				$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_SUBSCRIPTION_RECURRING_GUEST_TEMPLATE, $storeId);
			} else {
				$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_SUBSCRIPTION_GUEST_TEMPLATE, $storeId);
			}
			$customerName = $this->getBillingAddress()->getName();
		}
		else {
			if (Mage::registry('customweb_subscription_recurring_order') === true) {
				$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_SUBSCRIPTION_RECURRING_TEMPLATE, $storeId);
			} else {
				$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_SUBSCRIPTION_TEMPLATE, $storeId);
			}
			$customerName = $this->getCustomerName();
		}

		$mailer = Mage::getModel('core/email_template_mailer');
		$emailInfo = Mage::getModel('core/email_info');
		$emailInfo->addTo($this->getCustomerEmail(), $customerName);
		if ($copyTo && $copyMethod == 'bcc') {
			// Add bcc to customer email
			foreach ($copyTo as $email) {
				$emailInfo->addBcc($email);
			}
		}
		$mailer->addEmailInfo($emailInfo);

		// Email copies are sent as separated emails if their copy method is 'copy'
		if ($copyTo && $copyMethod == 'copy') {
			foreach ($copyTo as $email) {
				$emailInfo = Mage::getModel('core/email_info');
				$emailInfo->addTo($email);
				$mailer->addEmailInfo($emailInfo);
			}
		}

		$subscription = Mage::getModel('customweb_subscription/subscription')->loadByOrder($this->getId());

		// Set all required params and send emails
		$mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId($templateId);
		$mailer->setTemplateParams(
				array(
					'order' => $this,
					'billing' => $this->getBillingAddress(),
					'payment_html' => $paymentBlockHtml,
					'subscription' => $subscription,
					'subscription_status' => Mage::helper('customweb_subscription/render')->getStatusLabel($subscription->getStatus()),
					'subscription_period' => Mage::helper('customweb_subscription/render')->renderPlan($subscription->getPlan()),
					'subscription_end' => Mage::helper('customweb_subscription/render')->renderPlanEnd($subscription->getPlan()),
					'cancel_period' => Mage::helper('customweb_subscription')->__('Repeats %s time(s) after cancelation request.',
							$subscription->getCancelPeriod())
				));
		$mailer->send();

		$this->setEmailSent(true);
		$this->_getResource()->saveAttribute($this, 'email_sent');

		return $this;
	}
}