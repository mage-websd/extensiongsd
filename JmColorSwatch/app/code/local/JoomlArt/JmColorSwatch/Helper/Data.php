<?php
class JoomlArt_JmColorSwatch_Helper_Data extends Mage_Core_Helper_Abstract
{
	 function getassociatedproducts($productid){
         $product = Mage::getModel('catalog/product')->load($productid);
         if($product->isConfigurable()){
         	$groupedProductId = $productid;
            $coreResource = Mage::getSingleton('core/resource');
	        $conn = $coreResource->getConnection('core_read');
	        $select = $conn->select()
	            ->from($coreResource->getTableName('catalog/product_relation'), array('child_id'))
	            ->where('parent_id = ?', $groupedProductId);

	        $product_arr = $conn->fetchCol($select);
            return $product_arr; 
	        

         }  
	 }

	 function getRelatedproducts($productid){

        $product = Mage::getModel('catalog/product')->load($productid);
        /* @var $product Mage_Catalog_Model_Product */
        if(!Mage::helper("jmcolorswatch")->get('show')) return 0;  
        $itemCollection = $product->getRelatedProductCollection()
            ->addAttributeToSelect('required_options')
            ->setPositionOrder()
            ->addStoreFilter()
        ;

        //Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($itemCollection);

        $itemCollection->load();
        
        foreach ($itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
  
        return $itemCollection;   
	 }
     function get($var, $attributes){
        if(isset($attributes[$var])){
            return $attributes[$var];
        }       
        return Mage::getStoreConfig("joomlart_jmcolorswatch/joomlart_jmcolorswatch/$var");
     }     
}
	 