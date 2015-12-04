<?php

class CJM_ColorSelectorPlus_Block_Adminhtml_Catalog_Product_Attribute_Edit_Form extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return Mage_Adminhtml_Block_Widget_Form::_prepareForm();
    }

}
