<?php
class CJM_ColorSelectorPlus_Model_Position
{
	public function toOptionArray()
    {
        return array(
            array('value'=>'right', 'label'=>Mage::helper('colorselectorplus')->__('Right')),
            array('value'=>'left', 'label'=>Mage::helper('colorselectorplus')->__('Left')),
            array('value'=>'top', 'label'=>Mage::helper('colorselectorplus')->__('Top')),
            array('value'=>'bottom', 'label'=>Mage::helper('colorselectorplus')->__('Bottom')),
            array('value'=>'inside', 'label'=>Mage::helper('colorselectorplus')->__('Inside'))
        );
    }
}
