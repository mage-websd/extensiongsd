<?php
class JoomlArt_JmProductDetail_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

        $requests =  $this->getRequest()->getParams();
        $product_arr = Mage::helper("jmproductdetail")->getassociatedproducts($requests["mainproduct"]);
        
        if(is_array($product_arr)){
          foreach ($product_arr as $productid) {
            $product = Mage::getModel('catalog/product')->load($productid);
            echo $product->getData("size");
          }
        }

	  
    }
}