<?php
class CJM_ColorSelectorPlus_Model_Truefalse
{
	public function toOptionArray()
    {
        return array(
            array('value'=>'true', 'label'=>Mage::helper('colorselectorplus')->__('True')),
            array('value'=>'false', 'label'=>Mage::helper('colorselectorplus')->__('False'))
        );
    }
}
