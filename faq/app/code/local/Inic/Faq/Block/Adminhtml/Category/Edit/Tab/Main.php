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
class Inic_Faq_Block_Adminhtml_Category_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preparation of current form
     *
     * @return Inic_Faq_Block_Adminhtml_Category_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('faq_category');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('faq_');

        $fieldset = $form->addFieldset('base_fieldset', array (
                'legend' => Mage::helper('faq')->__('General information'),
                'class' => 'fieldset-wide' ));

        if ($model->getCategoryId()) {
            $fieldset->addField('category_id', 'hidden', array (
                    'name' => 'category_id'
            ));
        }



        $fieldset->addField('category_name', 'text', array (
            'name' => 'category_name',
            'label' => Mage::helper('faq')->__('Category Name'),
            'title' => Mage::helper('faq')->__('Category Name'),
            'required' => true,
        ));


        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect',
                    array (
                            'name' => 'store_id[]',
                            'label' => Mage::helper('faq')->__('Store view'),
                            'title' => Mage::helper('faq')->__('Store view'),
                            'required' => true,
                            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true) ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array (
                    'name' => 'store_id[]',
                    'value' => Mage::app()->getStore(true)->getId() ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('is_active', 'select',
                array (
                        'label' => Mage::helper('faq')->__('Status'),
                        'title' => Mage::helper('faq')->__('Category Status'),
                        'name' => 'is_active',
                        'required' => true,
                        'options' => array (
                                '1' => Mage::helper('faq')->__('Enabled'),
                                '0' => Mage::helper('faq')->__('Disabled') ) ));
                                $fieldset->addField('position', 'text',
            array (
                'name' => 'position',
                'label' => Mage::helper('faq')->__('Position'),
                'title' => Mage::helper('faq')->__('Position'),
                'required' => false,
            ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
