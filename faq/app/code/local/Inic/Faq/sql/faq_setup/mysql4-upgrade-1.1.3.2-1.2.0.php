<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013 Indianic
 */

$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('faq/faq')}` ADD `position` INT(11) NULL;
ALTER TABLE `{$this->getTable('faq/category')}` ADD `position` INT(11) NULL;
");
$installer->endSetup();
