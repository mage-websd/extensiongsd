<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('deliverydate')};
CREATE TABLE {$this->getTable('deliverydate')} (
  `deliverydate_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`deliverydate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
    
$this->_conn->addColumn($this->getTable('sales_flat_quote'), 'shipping_arrival_date', 'datetime');
$this->_conn->addColumn($this->getTable('sales_flat_quote'), 'shipping_arrival_comments', 'text');
$this->_conn->addColumn($this->getTable('sales_flat_order'), 'shipping_arrival_date', 'datetime');
$this->_conn->addColumn($this->getTable('sales_flat_order'), 'shipping_arrival_comments', 'text');

$installer->endSetup(); 