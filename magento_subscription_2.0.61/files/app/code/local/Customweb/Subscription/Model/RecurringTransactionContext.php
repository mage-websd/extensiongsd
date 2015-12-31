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
 * Extends the transaction context wrapper to change the success and failure url
 * for recurring payments done over the frontend.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_RecurringTransactionContext extends Customweb_Subscription_Model_TransactionContext {

	public function getSuccessUrl(){
		return Mage::getUrl('subscription/payment/success', array(
			'_secure' => true,
			'subscription_id' => $this->_subscription->getId() 
		));
	}

	public function getFailedUrl(){
		return Mage::getUrl('subscription/payment/fail', array(
			'_secure' => true,
			'subscription_id' => $this->_subscription->getId() 
		));
	}
}