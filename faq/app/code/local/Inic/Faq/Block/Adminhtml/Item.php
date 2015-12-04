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
class Inic_Faq_Block_Adminhtml_Item extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Constructor for FAQ Adminhtml Block
     */
    public function __construct()
    {
        $this->_blockGroup = 'faq';
        $this->_controller = 'adminhtml_item';
        $this->_headerText = Mage::helper('faq')->__('Manage FAQ Items');
        $this->_addButtonLabel = Mage::helper('faq')->__('Add New FAQ Item');
        
        parent::__construct();
    }

    /**
     * Returns the CSS class for the header
     * 
     * Usually 'icon-head' and a more precise class is returned. We return
     * only an empty string to avoid spacing on the left of the header as we
     * don't have an icon.
     * 
     * @return string
     */
    public function getHeaderCssClass()
    {
        return '';
    }
}
