<?php

class CJM_ColorSelectorPlus_Model_Catalog_Product_Url extends Mage_Catalog_Model_Product_Url
{
    public function getProductUrl($product, $useSid = null)
    {
        if ($useSid === null) {
            $useSid = Mage::app()->getUseSessionInUrl(); }

      	$params = array();
        $query = Mage::helper('colorselectorplus')->getQueryString($product);
		
		if (!$useSid) {
            $params['_nosid'] = true; }
		
		if($query != '') {
			$params['_query'] = $query; }
	
        return $this->getUrl($product, $params);
    }
}
