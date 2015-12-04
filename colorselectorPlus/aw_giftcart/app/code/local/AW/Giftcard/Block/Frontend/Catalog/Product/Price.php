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

class AW_Giftcard_Block_Frontend_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    public function getMinAmount()
    {
        if ($this->getProduct()->getData('aw_gc_allow_open_amount')) {
            return $this->getProduct()->getData('aw_gc_open_amount_min');
        }
        $min = null;
        $amountOptions = $this->_getAmountOptions();
        foreach ($amountOptions as $amount) {
            if ($min == null || $min > $amount['value']) {
                $min = $amount['value'];
            }
        }
        return $min;
    }

    public function getMaxAmount()
    {
        if ($this->getProduct()->getData('aw_gc_allow_open_amount')) {
            return $this->getProduct()->getData('aw_gc_open_amount_max');
        }
        $max = null;
        $amountOptions = $this->_getAmountOptions();
        foreach ($amountOptions as $amount) {
            if ($max == null || $max < $amount['value']) {
                $max = $amount['value'];
            }
        }
        return $max;
    }

    protected function _getAmountOptions()
    {
        return $this->getProduct()->getPriceModel()->getAmountOptions($this->getProduct());
    }
}