<?php
$installer = $this;
$installer->startSetup();


$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_giftcard/emailqueue')} (
      `entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `order_id` INT(11),
      `order_increment_id` VARCHAR(255),
      `template_data` text NOT NULL,
      `store` text NOT NULL,
      `item` INT(11),
      `schedule` TIMESTAMP NOT NULL,
      `created_at` TIMESTAMP NOT NULL,
      `process_at` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`entity_id`)
      )ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
");
$installer->endSetup();
