<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Giftcard
 * @version    1.0.8
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'aw_gc_amounts',
    array(
        'group'                   => 'Prices',
        'type'                    => 'decimal',
        'backend'                 => 'aw_giftcard/attribute_backend_product_amount',
        'frontend'                => '',
        'label'                   => 'Amounts',
        'input'                   => 'price',
        'class'                   => '',
        'source'                  => '',
        'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'                 => true,
        'required'                => false,
        'user_defined'            => false,
        'default'                 => '',
        'searchable'              => false,
        'filterable'              => false,
        'comparable'              => false,
        'visible_on_front'        => false,
        'unique'                  => false,
        'apply_to'                => 'aw_giftcard',
        'is_configurable'         => false,
        'used_in_product_listing' => true,
        'sort_order'              => 1,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_allow_open_amount',
    array(
        'group'                   => 'Prices',
        'type'                    => 'int',
        'backend'                 => '',
        'frontend'                => '',
        'label'                   => 'Allow Open Amount',
        'input'                   => 'select',
        'class'                   => '',
        'source'                  => 'aw_giftcard/source_product_attribute_option_yesno',
        'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'                 => true,
        'required'                => true,
        'user_defined'            => false,
        'default'                 => '',
        'searchable'              => false,
        'filterable'              => false,
        'comparable'              => false,
        'visible_on_front'        => false,
        'unique'                  => false,
        'apply_to'                => 'aw_giftcard',
        'is_configurable'         => false,
        'used_in_product_listing' => true,
        'sort_order'              => 2,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_open_amount_min',
    array(
        'group'                   => 'Prices',
        'type'                    => 'decimal',
        'backend'                 => 'aw_giftcard/attribute_backend_product_price',
        'frontend'                => '',
        'label'                   => 'Open Amount Min Value',
        'input'                   => 'price',
        'class'                   => 'validate-number validate-greater-than-zero',
        'source'                  => '',
        'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'                 => true,
        'required'                => false,
        'user_defined'            => false,
        'default'                 => '',
        'searchable'              => false,
        'filterable'              => false,
        'comparable'              => false,
        'visible_on_front'        => false,
        'unique'                  => false,
        'apply_to'                => 'aw_giftcard',
        'is_configurable'         => false,
        'used_in_product_listing' => true,
        'sort_order'              => 3,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_open_amount_max',
    array(
        'group'                   => 'Prices',
        'type'                    => 'decimal',
        'backend'                 => 'catalog/product_attribute_backend_price',
        'frontend'                => '',
        'label'                   => 'Open Amount Max Value',
        'input'                   => 'price',
        'class'                   => 'validate-number validate-greater-than-zero',
        'source'                  => '',
        'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'                 => true,
        'required'                => false,
        'user_defined'            => false,
        'default'                 => '',
        'searchable'              => false,
        'filterable'              => false,
        'comparable'              => false,
        'visible_on_front'        => false,
        'unique'                  => false,
        'apply_to'                => 'aw_giftcard',
        'is_configurable'         => false,
        'used_in_product_listing' => true,
        'sort_order'              => 4,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_type',
    array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Card Type',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'aw_giftcard/source_product_attribute_giftcard_type',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 1,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_expire',
    array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Expires After (days)',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 2,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_config_expire',
    array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Expires After',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 3,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_allow_message',
    array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Allow Message',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 4,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_config_allow_message',
    array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Allow Message',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 5,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_email_template',
    array(
        'group'             => 'Prices',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Email Template',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 6,
    )
);
$installer->addAttribute('catalog_product', 'aw_gc_config_email_template',
    array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Email Template',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'aw_giftcard',
        'is_configurable'   => false,
        'sort_order'        => 7,
    )
);

$applyTo = $installer->getAttribute('catalog_product', 'weight', 'apply_to');
if ($applyTo) {
    $applyTo = explode(',', $applyTo);
    if (!in_array('aw_giftcard', $applyTo)) {
        $applyTo[] = 'aw_giftcard';
        $installer->updateAttribute('catalog_product', 'weight', 'apply_to', join(',', $applyTo));
    }
}

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/giftcard')} (
      `entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `code` VARCHAR(255) NOT NULL,
      `status` SMALLINT(6) NOT NULL DEFAULT '0',
      `created_at` DATE NOT NULL,
      `expire_at` DATE NULL DEFAULT NULL,
      `website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
      `balance` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
      `state` SMALLINT(6) NOT NULL DEFAULT '0',
      PRIMARY KEY (`entity_id`),
      INDEX `IDX_AW_GIFTCARD_WEBSITE_ID` (`website_id` ASC))
    ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/history')} (
      `history_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `giftcard_id` INT(10) UNSIGNED NOT NULL,
      `updated_at` TIMESTAMP NULL,
      `action` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
      `balance_amount` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
      `balance_delta` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
      `additional_info` VARCHAR(255) NULL DEFAULT NULL,
      PRIMARY KEY (`history_id`, `giftcard_id`),
      INDEX `fk_aw_giftcard_history_aw_giftcard_idx` (`giftcard_id` ASC),
      CONSTRAINT `fk_aw_giftcard_history_aw_giftcard`
        FOREIGN KEY (`giftcard_id`)
        REFERENCES {$this->getTable('aw_giftcard/giftcard')} (`entity_id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/quote_giftcard')} (
      `link_id` INT(10) UNSIGNED NOT NULL  AUTO_INCREMENT,
      `giftcard_id` INT(10) UNSIGNED NOT NULL,
      `quote_entity_id` INT(10) UNSIGNED NOT NULL,
      `base_giftcard_amount` DECIMAL(12,4) UNSIGNED NULL,
      `giftcard_amount` DECIMAL(12,4) UNSIGNED NULL,
      PRIMARY KEY (`link_id`),
      INDEX `fk_aw_quote_totals_aw_giftcard1_idx` (`giftcard_id` ASC),
      CONSTRAINT `fk_aw_qoute_totals_aw_giftcard1`
        FOREIGN KEY (`giftcard_id`)
        REFERENCES {$this->getTable('aw_giftcard/giftcard')} (`entity_id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/order_invoice_giftcard')} (
      `link_id` INT(10) UNSIGNED NOT NULL  AUTO_INCREMENT,
      `giftcard_id` INT(10) UNSIGNED NOT NULL,
      `invoice_entity_id` INT(10) UNSIGNED NOT NULL,
      `base_giftcard_amount` DECIMAL(12,4) UNSIGNED NULL,
      `giftcard_amount` DECIMAL(12,4) UNSIGNED NULL,
      PRIMARY KEY (`link_id`),
      INDEX `fk_aw_invoice_totals_aw_giftcard1_idx` (`giftcard_id` ASC),
      CONSTRAINT `fk_aw_invoice_totals_aw_giftcard1`
        FOREIGN KEY (`giftcard_id`)
        REFERENCES {$this->getTable('aw_giftcard/giftcard')} (`entity_id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/order_creditmemo_giftcard')} (
      `link_id` INT(10) UNSIGNED NOT NULL  AUTO_INCREMENT,
      `giftcard_id` INT(10) UNSIGNED NOT NULL,
      `creditmemo_entity_id` INT(10) UNSIGNED NOT NULL,
      `base_giftcard_amount` DECIMAL(12,4) UNSIGNED NULL,
      `giftcard_amount` DECIMAL(12,4) UNSIGNED NULL,
      PRIMARY KEY (`link_id`),
      INDEX `fk_aw_creditmemo_totals_aw_giftcard1_idx` (`giftcard_id` ASC),
      CONSTRAINT `fk_aw_creditmemo_totals_aw_giftcard1`
        FOREIGN KEY (`giftcard_id`)
        REFERENCES {$this->getTable('aw_giftcard/giftcard')} (`entity_id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/product_amount')} (
      `link_id` int(11) NOT NULL auto_increment,
      `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
      `value` decimal(12,4) NOT NULL DEFAULT '0.0000',
      `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
      `entity_type_id` smallint (5) unsigned NOT NULL,
      `attribute_id` smallint (5) unsigned NOT NULL,
      PRIMARY KEY  (`link_id`),
      KEY `fk_aw_giftcard_product_amount_product_entity` (`entity_id`),
      KEY `fk_aw_giftcard_product_amount_website` (`website_id`),
      KEY `fk_aw_giftcard_product_amount_attribute_id` (`attribute_id`),
      CONSTRAINT `fk_aw_giftcard_product_amount_product_entity` FOREIGN KEY (`entity_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `fk_aw_giftcard_product_amount_website` FOREIGN KEY (`website_id`) REFERENCES {$this->getTable('core_website')} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `fk_aw_giftcard_product_amount_attribute_id` FOREIGN KEY (`attribute_id`) REFERENCES {$this->getTable('eav_attribute')} (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
