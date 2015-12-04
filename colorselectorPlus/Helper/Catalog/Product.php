<?php

class CJM_ColorSelectorPlus_Helper_Catalog_Product extends Mage_Catalog_Helper_Product
{	
	public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new Varien_Object();
        }

        // Init and load product
        Mage::dispatchEvent('catalog_controller_product_init_before', array(
            'controller_action' => $controller,
            'params' => $params,
        ));

        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
			
		if($product->type_id=="simple")
		{
        	$configurable_product = Mage::getModel('catalog/product_type_configurable');
			$parentIdArray = $configurable_product->getParentIdsByChild($product->getId());
			
			//var_dump($parentIdArray);
			
			//$parentId = $product->loadParentProductIds()->getData('parent_product_ids');
        	if(isset($parentIdArray[0]))
        	{
            	$product = Mage::getModel('catalog/product')->load($parentIdArray[0]);
        	}
    	}

        if (!$this->canShow($product)) {
            return false;
        }
        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        // Load product current category
        $categoryId = $params->getCategoryId();
        if (!$categoryId && ($categoryId !== false)) {
            $lastId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
            if ($product->canBeShowInCategory($lastId)) {
                $categoryId = $lastId;
            }
        } elseif (!$product->canBeShowInCategory($categoryId)) {
            $categoryId = null;
        }

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
            Mage::register('current_category', $category);
        }

        // Register current data and dispatch final events
        Mage::register('current_product', $product);
        Mage::register('product', $product);

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product' => $product));
            Mage::dispatchEvent('catalog_controller_product_init_after',
                            array('product' => $product,
                                'controller_action' => $controller
                            )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }
}