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

class AW_Giftcard_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_EXPIRE                  = 'aw_giftcard/general/expire';
    const GENERAL_ALLOW_GIFT_MESSAGE      = 'aw_giftcard/general/allow_gift_message';
    const GENERAL_ALLOW_ORDER_ITEM_STATUS = 'aw_giftcard/general/allow_order_item_status';
    const GENERAL_ALLOW_REFUND            = 'aw_giftcard/general/allow_refund';

    const COUPON_CODE_LENGTH              = 'aw_giftcard/coupon/code_length';
    const COUPON_CODE_FORMAT              = 'aw_giftcard/coupon/code_format';
    const COUPON_CODE_PREFIX              = 'aw_giftcard/coupon/code_prefix';
    const COUPON_CODE_SUFFIX              = 'aw_giftcard/coupon/code_suffix';
    const COUPON_CODE_SEPARATOR           = 'aw_giftcard/coupon/code_separator';
    const COUPON_CODE_DASH                = 'aw_giftcard/coupon/code_dash';

    const EMAIL_SENDER                    = 'aw_giftcard/email/sender';
    const EMAIL_TEMPLATE                  = 'aw_giftcard/email/template';

    public function getExpireValue($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::GENERAL_EXPIRE);
    }

    public function isAllowGiftMessage($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_ALLOW_GIFT_MESSAGE, $store);
    }

    public function getAllowOrderItemStatus($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::GENERAL_ALLOW_ORDER_ITEM_STATUS);
    }

    public function getAllowRefund($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::GENERAL_ALLOW_REFUND);
    }

    public function getCouponCodeLength($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::COUPON_CODE_LENGTH);
    }

    public function getCouponCodeFormat($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::COUPON_CODE_FORMAT);
    }

    public function getCouponCodePrefix($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::COUPON_CODE_PREFIX);
    }

    public function getCouponCodeSuffix($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::COUPON_CODE_SUFFIX);
    }

    public function getCouponCodeSeparator($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::COUPON_CODE_SEPARATOR);
    }

    public function getCouponCodeDash($website = null)
    {
        if (null === $website) {
            $website = Mage::app()->getWebsite();
        }
        return $website->getConfig(self::COUPON_CODE_DASH);
    }

    public function getEmailSender($store = null)
    {
        return Mage::getStoreConfig(self::EMAIL_SENDER, $store);
    }

    public function getEmailTemplate($store = null)
    {
        return Mage::getStoreConfig(self::EMAIL_TEMPLATE, $store);
    }
}