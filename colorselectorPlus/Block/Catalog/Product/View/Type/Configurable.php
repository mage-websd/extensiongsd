<?php

class CJM_ColorSelectorPlus_Block_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
	public function getJsonConfig()
    {
        $config = parent::getJsonConfig();
        $config = Mage::helper('core')->jsonDecode($config);
        $attributes = $config['attributes'];
        foreach ($this->getAllowAttributes() as $attribute):
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $theCount = count($attributes[$attributeId]['options']);
            if (isset($attributes[$attributeId])){ $attributes[$attributeId]['preselect'] = $theCount > 1 ? $attribute->getPreselect() : 'one'; }
        endforeach;
        $config['attributes'] = $attributes;
        return Mage::helper('core')->jsonEncode($config);
    }
}