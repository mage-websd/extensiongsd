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
interface Customweb_Subscription_Model_ISubscription {

	/**
	 * Email identity config path
	 *
	 * @var string
	 */
	const XML_PATH_EMAIL_IDENTITY = 'customweb_subscription/email/identity';

	/**
	 * Subscription status contants
	 */
	const STATUS_UNKNOWN = 'unknown';

	const STATUS_ACTIVE = 'active';
	const STATUS_PENDING = 'pending';
	const STATUS_CANCELED = 'canceled';
	const STATUS_SUSPENDED = 'suspended';
	const STATUS_FAILED = 'failed';
	const STATUS_ERROR = 'error';
	const STATUS_EXPIRED = 'expired';
	const STATUS_PAID = 'paid';
	const STATUS_AUTHORIZED = 'authorized';
}