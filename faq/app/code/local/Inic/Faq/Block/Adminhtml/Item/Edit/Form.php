<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013 Indianic
 */

/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @author     Inic
 */
class Inic_Faq_Block_Adminhtml_Item_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preperation of current form
     *
     * @return Inic_Faq_Block_Adminhtml_Item_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array (
                'id' => 'edit_form', 
                'action' => $this->getData('action'), 
                'method' => 'post', 
                'enctype' => 'multipart/form-data' ));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
