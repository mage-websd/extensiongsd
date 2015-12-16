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
class Inic_Faq_Block_Adminhtml_Item_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepares the page layout
     *
     * Loads the WYSIWYG editor on demand if enabled.
     *
     * @return Inic_Faq_Block_Admin_Edit
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $this;
    }

    /**
     * Preparation of current form
     *
     * @return Inic_Faq_Block_Admin_Edit_Tab_Main Self
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('faq');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('faq_');

        $fieldset = $form->addFieldset('base_fieldset', array (
                'legend' => Mage::helper('faq')->__('General information'),
                'class' => 'fieldset-wide' ));

        if ($model->getFaqId()) {
            $fieldset->addField('faq_id', 'hidden', array (
                    'name' => 'faq_id' ));
        }

        $approveInfo= $fieldset->addField('question', 'text',
        	array (
                'name' => 'question',
                'label' => Mage::helper('faq')->__('FAQ item question'),
                'title' => Mage::helper('faq')->__('FAQ item question'),
                'required' => true
            ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $store_id = $fieldset->addField('store_id', 'multiselect',
                    array (
                            'name' => 'store_id[]',
                            'label' => Mage::helper('faq')->__('Store view'),
                            'title' => Mage::helper('faq')->__('Store view'),
                            'required' => true,
                            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
                            ));

        }
        else {
            $store_id = $fieldset->addField('store_id', 'hidden',
            	array (
                    'name' => 'store_id[]',
                    'value' => Mage::app()->getStore(true)->getId()
            	));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $status =$fieldset->addField('is_active', 'select',
                array (
                        'label' => Mage::helper('faq')->__('Status'),
                        'title' => Mage::helper('faq')->__('Item status'),
                        'name' => 'is_active',
                        'required' => true,
                        'options' => array (
                                '1' => Mage::helper('faq')->__('Enabled'),
                                '0' => Mage::helper('faq')->__('Disabled') )
                       ));

		$fieldset->addField('is_most_frequent', 'select',
                array (
                        'label' => Mage::helper('faq')->__('Is Most Frequent'),
                        'title' => Mage::helper('faq')->__('Is Most Frequent'),
                        'name' => 'is_most_frequent',
                        'required' => true,
                        'options' => array (
                                '1' => Mage::helper('faq')->__('Yes'),
                                '0' => Mage::helper('faq')->__('No') ) ));
        $fieldset->addField('position', 'text',
            array (
                'name' => 'position',
                'label' => Mage::helper('faq')->__('Position'),
                'title' => Mage::helper('faq')->__('Position'),
                'required' => false,
            ));
        $category_id = $fieldset->addField('category_id', 'multiselect',
			            array (
			                'label' => Mage::helper('faq')->__('Category'),
			                'title' => Mage::helper('faq')->__('Category'),
			                'name' => 'category_id[]',
			                'required' => false,
			                'values' => Mage::getResourceSingleton('faq/category_collection')->toOptionArray(),
			            )
			        );


        $fieldset->addField('answer', 'editor',
                array (
                        'name' => 'answer',
                        'label' => Mage::helper('faq')->__('Content'),
                        'title' => Mage::helper('faq')->__('Content'),
                        'style' => 'height:36em;',
                        'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                        'state'     => 'html',
                        'required' => true,
                       ));
        $data = $model->getData();
        if(!count($data))
        {
        	$data['store_id'] = 0;
        }
        $form->setValues($data);
        $this->setForm($form);

        $cat_id =$model->getData('category_id');
        $selected = $cat_id ? $cat_id : "";
        $this->setChild('form_after', $this->getLayout()->createBlock('faq/adminhtml_widget_form_element_selectdependence')
            ->addFieldMap($store_id->getHtmlId(), $category_id->getHtmlId(),$selected)
        );
        return parent::_prepareForm();
    }
}
