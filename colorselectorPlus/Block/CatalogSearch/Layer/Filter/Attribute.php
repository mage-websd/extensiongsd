<?php

class CJM_ColorSelectorPlus_Block_CatalogSearch_Layer_Filter_Attribute extends Mage_CatalogSearch_Block_Layer_Filter_Attribute
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('colorselectorplus/filter.phtml'); 
    }
    
    public function getTheItems()
    {
        $items = array(); 
        foreach (parent::getItems() as $_item){
            
            $attributeCode = $_item->getFilter()->getAttributeModel()->getAttributeCode();
            $optionId = $_item->getValueString();
            $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($attributeCode)->getFirstItem();
       		$attributeId = $attributeInfo->getAttributeId();
       		$theOption = Mage::getResourceModel('eav/entity_attribute_option_collection')->setPositionOrder('asc')->setAttributeFilter($attributeId)->setIdFilter($optionId)->setStoreFilter()->load()->getFirstItem();
        	$theLabel = $theOption['default_value'];
        	
        	preg_match_all('/((#?[A-Za-z0-9]+))/', $theLabel, $matches);
        	
        	if ( count($matches[0]) > 0 ) {
        		$color_value = $matches[1][count($matches[0])-1];
				$findme = '#';
				$pos = strpos($color_value, $findme);
			} else {
				$pos = false; }

            $item = array();
            $item['url']   = $this->htmlEscape($_item->getUrl());
            $item['label'] = $_item->getLabel();
            $item['code'] = $attributeCode;

            $item['count'] = '';
            if (!$this->getHideCounts())
                $item['count']  = ' (' . $_item->getCount() . ')';
            
            $item['image'] = '';
            $item['bgcolor'] = '';
            
            if(Mage::helper('colorselectorplus')->getSwatchUrl($optionId)):
                $item['image'] = Mage::helper('colorselectorplus')->getSwatchUrl($optionId);
            elseif($pos !== false):
                $item['bgcolor'] = $color_value;
           	else:
           		$item['image'] = Mage::helper('colorselectorplus')->getSwatchUrl('empty');
           	endif;
           
            $items[] = $item;
        }
        
        return $items;
    }
        
    public function getRequestValue()
    {
        return $this->_filter->getAttributeModel()->getAttributeCode();
    }
}
