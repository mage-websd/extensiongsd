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

class AW_Giftcard_Model_Mysql4_Indexer_Price
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
{
    protected function _getDependentAttributes()
    {
        return array(
            'aw_gc_allow_open_amount',
            'aw_gc_open_amount_min',
            'aw_gc_open_amount_max',
        );
    }

    public function registerEvent(Mage_Index_Model_Event $event)
    {
        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    $this->_registerCatalogProductSaveEvent($event);
                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    $this->_registerCatalogProductMassActionEvent($event);
                    break;
            }
        }
    }

    protected function _registerCatalogProductSaveEvent(Mage_Index_Model_Event $event)
    {
        $product      = $event->getDataObject();
        $attributes   = $this->_getDependentAttributes();
        $reindexPrice = $product->getAmountsHasChanged();
        foreach ($attributes as $code) {
            if ($product->dataHasChangedFor($code)) {
                $reindexPrice = true;
                break;
            }
        }

        if ($reindexPrice) {
            $event->addNewData('product_type_id', $product->getTypeId());
            $event->addNewData('reindex_price', 1);
        }
        return $this;
    }

    protected function _registerCatalogProductMassActionEvent(Mage_Index_Model_Event $event)
    {
        $actionObject = $event->getDataObject();
        $attributes   = $this->_getDependentAttributes();
        $reindexPrice = false;

        $attrData = $actionObject->getAttributesData();
        if (is_array($attrData)) {
            foreach ($attributes as $code) {
                if (array_key_exists($code, $attrData)) {
                    $reindexPrice = true;
                    break;
                }
            }
        }

        if ($reindexPrice) {
            $event->addNewData('reindex_price_product_ids', $actionObject->getProductIds());
        }
        return $this;
    }

    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id')
            )
        ;
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select
            ->columns(array('website_id'), 'cw')
            ->columns(array('tax_class_id' => new Zend_Db_Expr('0')))
            ->where('e.type_id = ?', $this->getTypeId())
        ;

        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);

        $openAmountMinExp   = $this->_addAttributeToSelect(
            $select, 'aw_gc_open_amount_min', 'e.entity_id', 'cs.store_id'
        );
        $openAmountMaxExp   = $this->_addAttributeToSelect(
            $select, 'aw_gc_open_amount_max', 'e.entity_id', 'cs.store_id'
        );
        $allowOpenAmountExp = $this->_addAttributeToSelect(
            $select, 'aw_gc_allow_open_amount', 'e.entity_id', 'cs.store_id'
        );

        $attrAmounts = $this->_getAttribute('aw_gc_amounts');

        $select->joinLeft(
            array('gca' => $this->getTable('aw_giftcard/product_amount')),
            'gca.entity_id = e.entity_id AND gca.attribute_id = '
            . $attrAmounts->getAttributeId()
            . ' AND (gca.website_id = cw.website_id OR gca.website_id = 0)',
            array()
        );

        $_finalPrice = $this->_getPriceExprForOpenAmountAttr($openAmountMinExp, 'MIN', $allowOpenAmountExp);

        $_columns = array(
            'price'       => $_finalPrice,
            'final_price' => $_finalPrice,
            'min_price'   => $_finalPrice,
            'max_price'   => $this->_getPriceExprForOpenAmountAttr($openAmountMaxExp, 'MAX', $allowOpenAmountExp),
            'tier_price'  => new Zend_Db_Expr('NULL'),
            'base_tier'   => new Zend_Db_Expr('NULL'),
        );

        if (AW_All_Helper_Versions::getPlatform() == AW_All_Helper_Versions::CE_PLATFORM
            && version_compare(Mage::getVersion(), '1.7', '>=')
        ) {
            $_columns = array(
                'orig_price'       => $_finalPrice,
                'price'            => $_finalPrice,
                'min_price'        => $_finalPrice,
                'max_price'        => $this->_getPriceExprForOpenAmountAttr(
                    $openAmountMaxExp, 'MAX', $allowOpenAmountExp),
                'tier_price'       => new Zend_Db_Expr('NULL'),
                'base_tier'        => new Zend_Db_Expr('NULL'),
                'group_price'      => new Zend_Db_Expr('NULL'),
                'base_group_price' => new Zend_Db_Expr('NULL'),
            );
        }

        if (AW_All_Helper_Versions::getPlatform() == AW_All_Helper_Versions::EE_PLATFORM
            && version_compare(Mage::getVersion(), '1.12', '>=')
        ) {
            $_columns = array(
                'price'            => $_finalPrice,
                'final_price'      => $_finalPrice,
                'min_price'        => $_finalPrice,
                'max_price'        => $this->_getPriceExprForOpenAmountAttr(
                    $openAmountMaxExp, 'MAX', $allowOpenAmountExp),
                'tier_price'       => new Zend_Db_Expr('NULL'),
                'base_tier'        => new Zend_Db_Expr('NULL'),
                'group_price'      => new Zend_Db_Expr('NULL'),
                'base_group_price' => new Zend_Db_Expr('NULL'),
            );
        }

        $select
            ->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'))
            ->columns($_columns)
        ;

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        Mage::dispatchEvent(
            'prepare_catalog_product_index_select',
            array(
                'select'        => $select,
                'entity_field'  => new Zend_Db_Expr('e.entity_id'),
                'website_field' => new Zend_Db_Expr('cw.website_id'),
                'store_field'   => new Zend_Db_Expr('cs.store_id')
            )
        );

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);

        return $this;
    }

    protected function _getPriceExprForOpenAmountAttr($attributeExp, $expression, $allowOpenAmountExp)
    {
        $amountsExpr    = $expression . '(IF(gca.link_id IS NULL, NULL, gca.value))';
        $openAmountExpr = $expression . '(IF(' . $allowOpenAmountExp . ' = 1, IF('
            . $attributeExp . ' > 0, ' . $attributeExp . ', 0), NULL))'
        ;

        $condition = $expression == 'MAX' ? ' < ' : ' > ';

        $priceExpr = new Zend_Db_Expr(
            'ROUND(IF(' . $openAmountExpr . ' IS NULL, IF(' . $amountsExpr . ' IS NULL, 0 ,'
            . $amountsExpr . '), IF(' . $amountsExpr . ' IS NULL, ' . $openAmountExpr
            . ', IF(' . $openAmountExpr . $condition . $amountsExpr . ', ' . $amountsExpr
            . ', ' . $openAmountExpr .'))),4)'
        );
        return $priceExpr;
    }
}