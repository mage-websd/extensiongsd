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
class Inic_Faq_Block_Adminhtml_Item_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructs current object
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('faq_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('faq')->__('FAQ item information'));
    }
    
    /**
     * Prepares the page layout
     * 
     * Adds the tabs to the left tab menu.
     * 
     * @return Inic_Faq_Block_Admin_Edit
     */
    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();

        $this->addTab(
            'main_section', 
            array(
                'label' => Mage::helper('faq')->__('General information'),
                'title' => Mage::helper('faq')->__('General information'),
                'content' => $this->getLayout()->createBlock('faq/adminhtml_item_edit_tab_main')->toHtml(),
                'active' => true,
            )
        );
        
        return $return;
    }
}
