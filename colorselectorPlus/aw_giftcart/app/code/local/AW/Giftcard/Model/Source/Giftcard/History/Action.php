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

class AW_Giftcard_Model_Source_Giftcard_History_Action
{
    const CREATED_VALUE  = 1;
    const UPDATED_VALUE  = 2;
    const USED_VALUE     = 3;
    const EXPIRED_VALUE  = 4;
    const REFUNDED_VALUE = 5;
    const REDEEMED_VALUE = 6;

    const CREATED_LABEL  = 'Created';
    const UPDATED_LABEL  = 'Updated';
    const USED_LABEL     = 'Used';
    const EXPIRED_LABEL  = 'Expired';
    const REFUNDED_LABEL = 'Refunded';
    const REDEEMED_LABEL = 'Redeemed';

    const BY_ADMIN_MESSAGE_VALUE            = 0;
    const BY_ORDER_MESSAGE_VALUE            = 1;
    const BY_CREDITMEMO_MESSAGE_VALUE       = 2;
    const BY_REDEEM_TO_STORECREDIT_VALUE    = 3;

    const BY_ADMIN_MESSAGE_LABEL            = 'By admin: %s.';
    const BY_ORDER_MESSAGE_LABEL            = 'Order #%s.';
    const BY_CREDITMEMO_MESSAGE_LABEL       = 'Refund for order #%s.';
    const BY_REDEEM_TO_STORECREDIT_LABEL    = 'Redeemed to Store Credit.';

    public function toOptionArray()
    {
        return array(
            self::CREATED_VALUE  => Mage::helper('aw_giftcard')->__(self::CREATED_LABEL),
            self::UPDATED_VALUE  => Mage::helper('aw_giftcard')->__(self::UPDATED_LABEL),
            self::USED_VALUE     => Mage::helper('aw_giftcard')->__(self::USED_LABEL),
            self::EXPIRED_VALUE  => Mage::helper('aw_giftcard')->__(self::EXPIRED_LABEL),
            self::REFUNDED_VALUE => Mage::helper('aw_giftcard')->__(self::REFUNDED_LABEL),
            self::REDEEMED_VALUE => Mage::helper('aw_giftcard')->__(self::REDEEMED_LABEL)
        );
    }

    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }

    public function getMessageLabelByType($messageType)
    {
        $label = '';
        switch ($messageType) {
            case self::BY_ADMIN_MESSAGE_VALUE :
                $label = self::BY_ADMIN_MESSAGE_LABEL;
                break;
            case self::BY_ORDER_MESSAGE_VALUE :
                $label = self::BY_ORDER_MESSAGE_LABEL;
                break;
            case self::BY_CREDITMEMO_MESSAGE_VALUE :
                $label = self::BY_CREDITMEMO_MESSAGE_LABEL;
                break;
            case self::BY_REDEEM_TO_STORECREDIT_VALUE :
                $label = self::BY_REDEEM_TO_STORECREDIT_LABEL;
                break;
        }
        return $label;
    }
}