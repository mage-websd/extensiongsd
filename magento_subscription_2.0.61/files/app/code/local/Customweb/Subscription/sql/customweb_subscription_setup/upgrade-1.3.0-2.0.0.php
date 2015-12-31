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

$installer->getConnection()->addColumn(
	$this->getTable('customweb_subscription/subscription'),
	'cancel_count',
	array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable' => false,
		'default' => '0',
		'comment' => 'Cancel Count',
	)
);

$installer->getConnection()->changeColumn(
	$this->getTable('customweb_subscription/subscription'),
	'initial_order',
	'initial_order_id',
	array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable'	=> false,
		'unsigned' 	=> true,
		'comment' => 'Initial Order Id',
	)
);

$installer->getConnection()->addColumn(
		$this->getTable('customweb_subscription/subscription'),
		'version',
		array(
			'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
			'length' => 5,
			'nullable' => false,
			'comment' => 'Version',
		)
);

/**
 * Install schedule table.
 */
$table = $installer->getConnection()
->newTable($installer->getTable('customweb_subscription/subscription_schedule'))
->addColumn('schedule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'identity'  => true,
	'unsigned'  => true,
	'nullable'  => false,
	'primary'   => true,
), 'Entity Id')
->addColumn('subscription_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => false,
), 'Subscription Id')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 7, array(
	'nullable'  => false,
	'default'	=> 'pending',
), 'Status')
->addColumn('messages', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => true,
), 'Messages')
->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Created At')
->addColumn('scheduled_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Scheduled At')
->addColumn('executed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Executed At')
->addColumn('finished_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Finished At')
->addColumn('count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'nullable'	=> false,
	'unsigned' 	=> true,
), 'Count')
->addColumn('action', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
	'nullable'  => false,
), 'Action')
->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => true,
), 'Additional Data')

->addIndex($installer->getIdxName('customweb_subscription/subscription_schedule', array('subscription_id')),
	array('subscription_id')
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription_schedule', 'subscription_id', 'customweb_subscription/subscription', 'entity_id'),
	'subscription_id', $installer->getTable('customweb_subscription/subscription'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->setComment('Customweb Subscription Schedule Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();