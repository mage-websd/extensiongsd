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
 * @package    AW_Points
 * @version    1.8.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $helper = Mage::helper('points');
        $attributes['quote_item_qty'] = $helper->__('Quantity in cart');
        $attributes['quote_item_price'] = $helper->__('Price in cart');
        $attributes['quote_item_row_total'] = $helper->__('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $product = Mage::getModel('catalog/product')
            ->load($object->getProductId())
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getPrice())
            ->setQuoteItemRowTotal($object->getRowTotal());

        return parent::validate($product);
    }
}
