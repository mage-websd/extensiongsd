<?php

$installer = $this;
$installer->startSetup();

$conn = $installer->getConnection();
$theTable = $this->getTable('catalog_product_entity_media_gallery_value');

if($conn->tableColumnExists($theTable, 'defaultimg')):
	Mage::log('Column defaultimg already exists!');
else:
	$conn->addColumn($theTable, 'defaultimg', 'TINYINT(4) UNSIGNED NOT NULL DEFAULT "0"');
endif;

if($conn->tableColumnExists($theTable, 'selectorbase')):
	Mage::log('Column selectorbase already exists!');
else:
	$conn->addColumn($theTable, 'selectorbase', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
endif;

if($conn->tableColumnExists($theTable, 'selectormore')):
	Mage::log('Column selectormore already exists!');
else:
	$conn->addColumn($theTable, 'selectormore', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
endif;

$installer->endSetup();