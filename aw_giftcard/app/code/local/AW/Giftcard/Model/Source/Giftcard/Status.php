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

class AW_Giftcard_Model_Source_Giftcard_Status
{
    const AVAILABLE_VALUE  = 1;
    const EXPIRED_VALUE    = 2;
    const USED_VALUE       = 3;
    const REFUNDED_VALUE   = 4;

    const AVAILABLE_LABEL = 'Available';
    const EXPIRED_LABEL   = 'Expired';
    const USED_LABEL      = 'Used';
    const REFUNDED_LABEL  = 'Refunded';

    const EXPIRED_ERROR_MESSAGE  = 'This card has expired';
    const USED_ERROR_MESSAGE     = 'The balance on this card is 0';
    const REFUNDED_ERROR_MESSAGE = 'This card has been refunded';
    const DEFAULT_ERROR_MESSAGE  = 'Gift Card is not valid.';

    public function toOptionArray()
    {
        return array(
            self::AVAILABLE_VALUE => Mage::helper('aw_giftcard')->__(self::AVAILABLE_LABEL),
            self::EXPIRED_VALUE   => Mage::helper('aw_giftcard')->__(self::EXPIRED_LABEL),
            self::USED_VALUE      => Mage::helper('aw_giftcard')->__(self::USED_LABEL),
            self::REFUNDED_VALUE  => Mage::helper('aw_giftcard')->__(self::REFUNDED_LABEL)
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

    public function getErrorMessage($value)
    {
        $result = self::DEFAULT_ERROR_MESSAGE;
        switch ($value) {
            case self::EXPIRED_VALUE : $result = self::EXPIRED_ERROR_MESSAGE;
                break;
            case self::USED_VALUE : $result = self::USED_ERROR_MESSAGE;
                break;
            case self::REFUNDED_VALUE : $result = self::REFUNDED_ERROR_MESSAGE;
                break;
        }
        return $result;
    }
}