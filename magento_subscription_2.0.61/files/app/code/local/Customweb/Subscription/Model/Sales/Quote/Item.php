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
 * Override the quote item to add custom behaviour.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Sales_Quote_Item extends Mage_Sales_Model_Quote_Item {

	/**
	 * Check whether this is a recurring product.
	 *
	 * @return boolean
	 */
	public function isSubscription(){
		if ($this->getProduct()->isConfigurable()) {
			return $this->getProduct()->isSubscription();
		}
		$product = Mage::getModel('catalog/product')->load($this->getProductId());
		return ($product && $product->isSubscription());
	}
}