<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	DELETE FROM {$installer->getTable('core/config_data')} WHERE path like 'ajaxfilter_cfg/ajax_conf/layered';
	DELETE FROM {$installer->getTable('core/config_data')} WHERE path like 'ajaxfilter_cfg/ajax_conf/price';
    INSERT INTO {$installer->getTable('core/config_data')} VALUES (NULL, 'default', '0', 'ajaxfilter_cfg/ajax_conf/layered', '1');
    INSERT INTO {$installer->getTable('core/config_data')} VALUES (NULL, 'default', '0', 'ajaxfilter_cfg/ajax_conf/price', '1');
	
");
$installer->endSetup();