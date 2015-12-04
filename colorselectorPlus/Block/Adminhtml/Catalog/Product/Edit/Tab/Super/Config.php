<?php

class CJM_ColorSelectorPlus_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
{
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('colorselectorplus/catalog/product/edit/super/config.phtml');
    }
    
    public function getAttributesJson()
    {
    	$configurable = $this->_getProduct()->getTypeInstance(true);
        $attributes = $configurable->getConfigurableAttributesAsArray($this->_getProduct());
        
        if(!$attributes) { return '[]'; }
        
        $options = array();
		
		foreach ($configurable->getConfigurableAttributes($this->_getProduct()) as $attribute):
			$options[$attribute->getId()] = $attribute->getPreselect();
		endforeach;
        
        foreach ($attributes as $a=>$b):
        	$attributes[$a]['preselect'] = isset($options[$b['id']]) ? $options[$b['id']] : null;
        endforeach;
        
        return Mage::helper('core')->jsonEncode($attributes);
    }
}
