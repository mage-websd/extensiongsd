<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
include_once('Mage/Catalog/controllers/ProductController.php');
class CJM_ColorSelectorPlus_Catalog_ProductController extends Mage_Catalog_ProductController
{
    /**
     * Initialize requested product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        Mage::dispatchEvent('catalog_controller_product_init_before', array('controller_action'=>$this));
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');

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
        	
			if(isset($parentIdArray[0]))
        	{
            	$product = Mage::getModel('catalog/product')->load($parentIdArray[0]);
        	}
    	}

        if (!Mage::helper('catalog/product')->canShow($product)) {
            return false;
        }
        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        $category = null;
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
            Mage::register('current_category', $category);
        }
        elseif ($categoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId()) {
            if ($product->canBeShowInCategory($categoryId)) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $product->setCategory($category);
                Mage::register('current_category', $category);
            }
        }


        Mage::register('current_product', $product);
        Mage::register('product', $product);

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product'=>$product));
            Mage::dispatchEvent('catalog_controller_product_init_after', array('product'=>$product, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }
}