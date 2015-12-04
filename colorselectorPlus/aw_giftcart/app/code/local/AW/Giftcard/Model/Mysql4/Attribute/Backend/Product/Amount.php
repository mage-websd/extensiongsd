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

class AW_Giftcard_Model_Mysql4_Attribute_Backend_Product_Amount extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_giftcard/product_amount', 'link_id');
    }

    public function loadProductAmountData($product, $attribute)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getMainTable(), array('website_id', 'value'))
            ->where('entity_id = ?', $product->getId())
            ->where('attribute_id = ?', $attribute->getId())
        ;

        if (!$attribute->isScopeGlobal()) {
            $websiteIds = array(0, Mage::app()->getStore($product->getStoreId())->getWebsiteId());
            $select->where('website_id IN(?)', $websiteIds);
        }
        return $read->fetchAll($select);
    }

    public function deleteProductAmountData($product, $attribute)
    {
        $queryData = array();

        if (!$attribute->isScopeGlobal()) {
            $queryData['website_id IN (?)'] = array(0, Mage::app()->getStore($product->getStoreId())->getWebsiteId());
        }

        $queryData['entity_id=?']    = $product->getId();
        $queryData['attribute_id=?'] = $attribute->getId();

        $this->_getWriteAdapter()->delete($this->getMainTable(), $queryData);
        return $this;
    }

    public function insertProductAmountData($product, $data)
    {
        $data['entity_id']      = $product->getId();
        $data['entity_type_id'] = $product->getEntityTypeId();

        $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        return $this;
    }
}