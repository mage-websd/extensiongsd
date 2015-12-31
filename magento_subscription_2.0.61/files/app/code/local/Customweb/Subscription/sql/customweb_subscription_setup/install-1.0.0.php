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

/**
 * Install product attributes.
 */
$installer->installEntities();

$installer->startSetup();

/**
 * Install subscription table.
 */
$table = $installer->getConnection()
->newTable($installer->getTable('customweb_subscription/subscription'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'identity'  => true,
	'unsigned'  => true,
	'nullable'  => false,
	'primary'   => true,
), 'Entity Id')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
	'nullable'  => false,
), 'Subscription Status')
->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => true,
), 'Customer Id')
->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => true,
	'default'   => '0',
), 'Store Id')
->addColumn('method_code', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
	'unsigned'  => true,
	'nullable'  => false,
), 'Store Id')
->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')
->addColumn('reference_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
), 'Reference Id')
->addColumn('subscriber_name', Varien_Db_Ddl_Table::TYPE_TEXT, 150, array(
	'nullable'  => true,
), 'Description')
->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
	'nullable'  => false,
), 'Description')
->addColumn('start_datetime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Start Datetime')
->addColumn('last_datetime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Last Datetime')
->addColumn('last_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => true,
), 'Last Order Id')
->addColumn('period_unit', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
	'nullable'  => false,
), 'Subscription Schedule')
->addColumn('period_frequency', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	'unsigned'  => true,
), 'Subscription Schedule')
->addColumn('period_max_cycles', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	'unsigned'  => true,
), 'Subscription Schedule')
->addColumn('billing_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
	'nullable'  => false,
        'default'   => '0.0000',
), 'Billing Amount')
->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
	'nullable'  => false,
), 'Currency Code')
->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Shipping Amount')
->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Tax Amount')
->addColumn('init_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Init Amount')
->addColumn('cancel_request', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
	'nullable'  => false,
	'default'   => '0',
), 'Cancellaction Requested')
->addColumn('cancel_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'Cancellaction Date')
->addColumn('cancel_period', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	'unsigned'  => true,
	'nullable'  => true,
), 'Cancellaction Date')
->addColumn('link_hash', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
	'nullable'  => true,
), 'Link Hash')
->addColumn('payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => true,
), 'Payment Id')
->addColumn('order_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => false,
), 'Order Info')
->addColumn('order_item_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => false,
), 'Order Item Info')
->addColumn('billing_address_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
	'nullable'  => false,
), 'Billing Address Info')
->addColumn('shipping_address_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
), 'Shipping Address Info')
->addIndex($installer->getIdxName('customweb_subscription/subscription', array('reference_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
	array('reference_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
->addIndex($installer->getIdxName('customweb_subscription/subscription', array('customer_id')),
	array('customer_id')
)
->addIndex($installer->getIdxName('customweb_subscription/subscription', array('store_id')),
	array('store_id')
)
->addIndex($installer->getIdxName('customweb_subscription/subscription', array('last_order_id')),
	array('last_order_id')
)
->addIndex($installer->getIdxName('customweb_subscription/subscription', array('payment_id')),
	array('payment_id')
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription', 'customer_id', 'customer/entity', 'entity_id'),
	'customer_id', $installer->getTable('customer/entity'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription', 'store_id', 'core/store', 'store_id'),
	'store_id', $installer->getTable('core/store'), 'store_id',
	Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription', 'last_order_id', 'sales/order', 'entity_id'),
		'last_order_id', $installer->getTable('sales/order'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription', 'payment_id', 'sales/order_payment', 'entity_id'),
	'payment_id', $installer->getTable('sales/order_payment'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->setComment('Customweb Subscription Table');
$installer->getConnection()->createTable($table);

/**
 * Install order table.
 */
$table = $installer->getConnection()
->newTable($installer->getTable('customweb_subscription/subscription_order'))
->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'identity'  => true,
	'unsigned'  => true,
	'nullable'  => false,
	'primary'   => true,
), 'Entity Id')
->addColumn('subscription_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => false,
	'default'   => '0',
), 'Subscription Id')
->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	'unsigned'  => true,
	'nullable'  => false,
	'default'   => '0',
), 'Order Id')
->addIndex($installer->getIdxName('customweb_subscription/subscription_order', array('subscription_id', 'order_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
	array('subscription_id', 'order_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
)
->addIndex($installer->getIdxName('customweb_subscription/subscription_order', array('subscription_id')),
	array('subscription_id')
)
->addIndex($installer->getIdxName('customweb_subscription/subscription_order', array('order_id')),
	array('order_id')
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription_order', 'subscription_id', 'customweb_subscription/subscription', 'entity_id'),
	'subscription_id', $installer->getTable('customweb_subscription/subscription'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->addForeignKey($installer->getFkName('customweb_subscription/subscription_order', 'order_id', 'sales/order', 'entity_id'),
	'order_id', $installer->getTable('sales/order'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
)
->setComment('Customweb Subscription Order Table');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'subscription_plan', array(
	'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
	'LENGTH'	=> '64k',
	'NULLABLE'  => true,
	'COMMENT'   => 'Subscription Plan'
));

$installer->endSetup();