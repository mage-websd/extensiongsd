<?php
class CJM_ColorSelectorPlus_Model_Smooth
{
	public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('colorselectorplus')->__('1')),
            array('value'=>2, 'label'=>Mage::helper('colorselectorplus')->__('2')),
            array('value'=>3, 'label'=>Mage::helper('colorselectorplus')->__('3'))
        );
    }
}
