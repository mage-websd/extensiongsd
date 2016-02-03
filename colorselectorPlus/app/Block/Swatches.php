<?php

class CJM_ColorSelectorPlus_Block_Swatches extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('colorselectorplus/swatches.phtml');
    }
}