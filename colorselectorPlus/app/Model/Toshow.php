<?php
class CJM_ColorSelectorPlus_Model_Toshow
{
    public static $_entityTypeId;
    public static $_productAttributes;
    public static $_productAttributeOptions;

    public static function getTheSwatchAttributes()
    {
        $swatch_attributes = array();
		$swatchattributes = Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/colorattributes',Mage::app()->getStore());
		$swatch_attributes = explode(",", $swatchattributes);
		
		foreach($swatch_attributes as $attribute) {
       		self::$_productAttributes[$attribute] = array(
         		'title' => Mage::getModel('eav/entity_attribute')->load($attribute)->getFrontendLabel(),
         		'code'  => Mage::getModel('eav/entity_attribute')->load($attribute)->getAttributeCode(),
         	);
     	}
     
		return self::$_productAttributes;
	}
	
	public static function toOptionArray()
    {
        if(is_array(self::$_productAttributeOptions)) return self::$_productAttributeOptions;

        self::$_productAttributeOptions = array();

        foreach(self::getTheSwatchAttributes() as $id => $data)
            self::$_productAttributeOptions[] = array(
                'value' => $id,
                'label' => $data['title'].' ('.$data['code'].')'
            );

        return self::$_productAttributeOptions;
    }
}