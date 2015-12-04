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
DROP TABLE IF EXISTS {$this->getTable('faq/faq')};
CREATE TABLE IF NOT EXISTS {$this->getTable('faq/faq')} (
  `faq_id` int(10) unsigned NOT NULL auto_increment,
  `question` tinytext NOT NULL default '',
  `answer` text NOT NULL default '',
  `creation_time` datetime default NULL,
  `update_time` datetime default NULL,
  `is_active` tinyint(1) NOT NULL default '1',
  `is_most_frequent` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`faq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='FAQ items' AUTO_INCREMENT=1 ;

ALTER TABLE `{$this->getTable('faq/faq')}`
    MODIFY COLUMN `creation_time` DATETIME NOT NULL;
ALTER TABLE `{$this->getTable('faq/faq')}`
    MODIFY COLUMN `update_time` DATETIME NOT NULL;

DROP TABLE IF EXISTS {$this->getTable('faq/faq_store')};
CREATE TABLE IF NOT EXISTS `{$this->getTable('faq/faq_store')}` (
  `faq_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`faq_id`,`store_id`),
  CONSTRAINT `FK_FAQ_FAQ_STORE_FAQ` FOREIGN KEY (`faq_id`) REFERENCES `{$this->getTable('faq/faq')}` (`faq_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='FAQ items to Stores';

DROP TABLE IF EXISTS `{$this->getTable('faq/category')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('faq/category')}` (
    `category_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` INT(10) UNSIGNED NULL,
    `category_name` VARCHAR(255) NOT NULL,
    `creation_time` DATETIME NOT NULL,
    `update_time` DATETIME NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`category_id`),
    CONSTRAINT `FK_FAQ_CATEGORY_PARENT_ID` FOREIGN KEY (`parent_id`) REFERENCES `{$this->getTable('faq/category')}` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='FAQ Categories';

DROP TABLE IF EXISTS `{$this->getTable('faq/category_item')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('faq/category_item')}` (
  `category_id` INT(10) UNSIGNED NOT NULL,
  `faq_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`category_id`,`faq_id`),
  CONSTRAINT `FK_FAQ_CATEGORY_ITEM_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES `{$this->getTable('faq/category')}` (`category_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_FAQ_CATEGORY_ITEM_ITEM` FOREIGN KEY (`faq_id`) REFERENCES `{$this->getTable('faq/faq')}` (`faq_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='FAQ Items to Cateories';

DROP TABLE IF EXISTS `{$this->getTable('faq/category_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('faq/category_store')}` (
  `category_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`),
  CONSTRAINT `FK_FAQ_CATEGORY_STORE_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES `{$this->getTable('faq/category')}` (`category_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='FAQ Categories to Stores';

");

$installer->endSetup();