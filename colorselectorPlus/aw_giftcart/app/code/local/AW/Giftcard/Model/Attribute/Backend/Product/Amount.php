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

class AW_Giftcard_Model_Attribute_Backend_Product_Amount extends Mage_Catalog_Model_Product_Attribute_Backend_Price
{
    protected function _getResource()
    {
        return Mage::getResourceSingleton('aw_giftcard/attribute_backend_product_amount');
    }

    public function validate($object)
    {
        $amounts = (array)$object->getData($this->getAttribute()->getName());
        $_amountsKeys = array();
        foreach ($amounts as $_amount) {
            if (!isset($_amount['price']) || !empty($_amount['delete'])) {
                continue;
            }

            $key = implode('-', array($_amount['website_id'], (float)$_amount['price']));
            if (array_key_exists($key, $_amountsKeys)) {
                Mage::throwException(
                    Mage::helper('aw_giftcard')->__('Duplicate amount found.')
                );
            }
            $_amountsKeys[$key] = true;
        }
        return $this;
    }

    public function afterLoad($object)
    {
        $loadedAmounts = $this->_getResource()->loadProductAmountData($object, $this->getAttribute());
        $object->setData($this->getAttribute()->getName(), $loadedAmounts);
        return $this;
    }

    public function afterSave($object)
    {
        $origData = $object->getOrigData($this->getAttribute()->getName());
        $currentData = $object->getData($this->getAttribute()->getName());

        if ($origData == $currentData) {
            return $this;
        }

        $this->_getResource()->deleteProductAmountData($object, $this->getAttribute());

        if (!is_array($currentData)) {
            return $this;
        }

        foreach ($currentData as $row) {
            if (!array_key_exists('price', $row) || !isset($row['price']) || !empty($row['delete'])) {
                continue;
            }

            $data = array(
                'website_id'   => $row['website_id'],
                'value'        => $row['price'],
                'attribute_id' => $this->getAttribute()->getId()
            );
            $this->_getResource()->insertProductAmountData($object, $data);
        }
        return $this;
    }

    public function afterDelete($object)
    {
        $this->_getResource()->deleteProductAmountData($object, $this->getAttribute());
        return $this;
    }
}