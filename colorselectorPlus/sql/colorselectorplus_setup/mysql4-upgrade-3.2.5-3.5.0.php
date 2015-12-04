<?php

$installer = $this;
$installer->startSetup();

if($installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_super_attribute_label'), 'preselect')):
	Mage::log('Column preselect already exists!');
else:
	$installer->getConnection()->addColumn($installer->getTable('catalog/product_super_attribute_label'), 'preselect', 'INT(10) unsigned');
endif;

$installer->endSetup();