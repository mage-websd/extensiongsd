<?php

class CJM_ColorSelectorPlus_Model_Swatches
{
    public function getPrefixes()
    {
   		$swatchez = Mage::helper('colorselectorplus')->getSwatchAttributes();
		foreach ($swatchez as $swatch) {
            $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($swatch)->getFirstItem();
            $prefixes[] = array(
                'field' => 'swatchsize_'.$swatch.'_',
                'label' => $attributeInfo['frontend_label'].' ('.$swatch.')',
            );
        }
	
		return $prefixes;
    }
}