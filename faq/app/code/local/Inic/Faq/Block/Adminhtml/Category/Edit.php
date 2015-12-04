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
class Inic_Faq_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor for the FAQ edit form
     */
    public function __construct()
    {
        $this->_objectId = 'category_id';
        $this->_blockGroup = 'faq';
        $this->_controller = 'adminhtml_category';
        
        parent::__construct();
        
        $this->_updateButton('save', 'label', Mage::helper('faq')->__('Save FAQ Category'));
        $this->_updateButton('delete', 'label', Mage::helper('faq')->__('Delete FAQ Category'));
        
        $this->_addButton('saveandcontinue', array (
                'label' => Mage::helper('faq')->__('Save and continue edit'), 
                'onclick' => 'saveAndContinueEdit()', 
                'class' => 'save' ), -100);
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    /**
     * Helper function to edit the header of the current form
     *
     * @return string Returns an "edit" or "new" text depending on the type of modifications.
     */
    public function getHeaderText()
    {
        if (Mage::registry('faq_category')->getFaqId()) {
            return Mage::helper('faq')->__("Edit FAQ Category '%s'", $this->htmlEscape(Mage::registry('faq_category')->getCategoryName()));
        }
        else {
            return Mage::helper('faq')->__('New FAQ Category');
        }
    }
    
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
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
