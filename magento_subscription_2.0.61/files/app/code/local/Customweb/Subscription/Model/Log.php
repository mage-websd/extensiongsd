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
 * Represents a subscription log entry.
 *
 * @author Simon Schurter
 *
 * @method int getId()
 * @method int getSubscriptionId()
 * @method Customweb_Subscription_Model_Log setSubscriptionId(int $value)
 * @method string getLevel()
 * @method Customweb_Subscription_Model_Log setLevel(string $value)
 * @method string getMessages()
 * @method Customweb_Subscription_Model_Log setMessages(string $value)
 * @method array getParameters()
 * @method Customweb_Subscription_Model_Log setParameters(array $value)
 * @method string getCreatedAt()
 * @method Customweb_Subscription_Model_Log setCreatedAt(string $value)
 */
class Customweb_Subscription_Model_Log extends Mage_Core_Model_Abstract {

	const LEVEL_DEBUG = 'debug';
	const LEVEL_INFO = 'info';
	const LEVEL_WARN = 'warn';
	const LEVEL_ERROR = 'error';

	/**
	 * Event prefix and object
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'customweb_subscription_log';
	protected $_eventObject = 'log';

	/**
	 *
	 * @var Customweb_Subscription_Model_Subscription
	 */
	private $_subscription = null;

	protected function _construct(){
		$this->_init('customweb_subscription/log');
	}

	/**
	 *
	 * @return Customweb_Subscription_Model_Subscription
	 */
	public function getSubscription(){
		if ($this->_subscription == null) {
			$this->_subscription = Mage::getModel('customweb_subscription/subscription')->load($this->getSubscriptionId());
		}
		return $this->_subscription;
	}

	/**
	 * Render the log message in the current language.
	 *
	 * @return string
	 */
	public function render(){
		$args = $this->getParameters();
		array_unshift($args, $this->getMessage());
		return call_user_func_array(array(Mage::helper('customweb_subscription'), '__'), $args);
	}

}