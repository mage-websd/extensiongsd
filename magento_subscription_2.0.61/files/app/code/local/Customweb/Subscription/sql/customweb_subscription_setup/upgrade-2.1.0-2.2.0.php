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

$installer = $this;

$installer->startSetup();

/**
 * Install log table.
 */
$table = $installer->getConnection()
->newTable($installer->getTable('customweb_subscription/subscription_log'))
->addColumn('log_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'identity'  => true,
	'unsigned'  => true,
	'nullable'  => false,
	'primary'   => true,
), 'Entity Id')
->addColumn('subscription_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => false,
), 'Subscription Id')
->addColumn('level', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
	'nullable'  => false,
	'default'	=> 'info',
), 'Level')
->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => true,
), 'Message')
->addColumn('parameters', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => true,
), 'Parameters')
->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Created At')

->addIndex($installer->getIdxName('customweb_subscription/subscription_schedule', array('subscription_id')),
	array('subscription_id')
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription_log', 'subscription_id', 'customweb_subscription/subscription', 'entity_id'),
	'subscription_id', $installer->getTable('customweb_subscription/subscription'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->setComment('Customweb Subscription Log Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();