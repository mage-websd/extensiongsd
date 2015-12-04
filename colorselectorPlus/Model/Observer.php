<?php

class CJM_ColorSelectorPlus_Model_Observer
{	
	public function core_collection_abstract_load_after($observer)
	{
		$collection = $observer->getCollection();
		if (get_class($collection) == 'Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection') {
        	if ($collection->count()) {
        		$labelTable = $collection->getTable('catalog/product_super_attribute_label');
	            $select = $collection->getConnection()->select()
	                ->from(array('default'=>$labelTable))
	                ->joinLeft(array('store' => $labelTable), 'store.product_super_attribute_id=default.product_super_attribute_id AND store.store_id='.$collection->getStoreId(), array('use_default' => new Zend_Db_Expr('IFNULL(store.use_default, default.use_default)'), 'label' => new Zend_Db_Expr('IFNULL(store.value, default.value)')))
	                ->where('default.product_super_attribute_id IN (?)', array_keys($collection->getItems()))
	                ->where('default.store_id=0');
	                foreach ($collection->getConnection()->fetchAll($select) as $data) {
	                    $collection->getItemById($data['product_super_attribute_id'])->setPreselect($data['preselect']);
	                }
	        }
        }
	}
	
	public function model_save_after($observer)
	{
		$attribute = $observer->getObject();
		if (get_class($attribute) == 'Mage_Catalog_Model_Product_Type_Configurable_Attribute')
        {
        	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
        	$labelTable = $attribute->getResource()->getTable('catalog/product_super_attribute_label');
        	$select = $write->select()
	            ->from($labelTable, 'value_id')
	            ->where('product_super_attribute_id=?', $attribute->getId())
	            ->where('store_id=?', (int)$attribute->getStoreId());
	        if ($valueId = $write->fetchOne($select)) {
	        	$write->update($labelTable,array('preselect' => (int) $attribute->getPreselect()), $write->quoteInto('value_id=?', $valueId)); }
	        else {
	            $write->insert($labelTable, array(
					'product_super_attribute_id' => $attribute->getId(),
	                'store_id' => (int) $attribute->getStoreId(),
	                'use_default' => (int) $attribute->getUseDefault(),
	                'value' => $attribute->getLabel(),
	                'preselect' => (int) $attribute->getPreselect()
	            ));
	        }
        }
	}
}