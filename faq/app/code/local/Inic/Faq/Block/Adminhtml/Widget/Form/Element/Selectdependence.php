<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013 Indianic

/**
 * Form Selectelement/multiselect dependencies mapper
 * Assumes that one element may depend on other element values.
 * Will toggle as "enabled" only if all elements it depends from toggle as true.
 */
class Inic_Faq_Block_Adminhtml_Widget_Form_Element_Selectdependence extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Parent id mapper
     * @var string
     */
    protected $fieldIdFrom;
    
    /**
     * dependent id mapper
     *
     * @var string
     */
    protected $fieldIdTo;

    /**
     * Dependencies mapper (by names)
     * array(
     *     'dependent_name' => array(
     *         'depends_from_1_name' => 'mixed value',
     *         'depends_from_2_name' => 'some another value',
     *         ...
     *     )
     * )
     * @var array
     */
    protected $_depends = array();

    /**
     * selected options
     *
     * @var array
     */
    protected $selectedField = array();
    /**
     * Add name => id mapping
     *
     * @param string $fieldIdFrom - Parent id mapper
     * @param string $fieldIdTo - dependent id mapper
     * @param string $selectedField - current Selected field
     * @return Mage_Adminhtml_Block_Widget_Form_Element_Dependence
     */
    public function addFieldMap($fieldIdFrom, $fieldIdTo,$selectedField)
    {
        $this->_fieldIdFrom = $fieldIdFrom;
        $this->_fieldIdTo = $fieldIdTo;
        $this->_selectedField = Mage::helper('core')->jsonEncode($selectedField);
        
        return $this;
    }
	
    /**
     * HTML output getter
     * @return string
     */
    protected function _toHtml()
    {
        return '<script type="text/javascript"> 
        		new SelectAutoUpdater('.$this->_fieldIdFrom.','.$this->_fieldIdTo.',"","",'. $this->_getDependsJson().','.$this->_selectedField. '); 
        		</script>';
    }

    /**
     * Field dependences JSON map generator
     * @return string
     */
    protected function _getDependsJson()
    {
        $stores=Mage::app()->getStores();
        $this->_depends[0] = Mage::getModel('faq/category')
        					->getCollection()
        					->toOptionHash();
        foreach ($stores as $id => $value){
        	$_storeId = Mage::app()->getStore($id)->getId();
        	$category = Mage::getModel('faq/category')
        					->getCollection()
        					->addStoreFilter($_storeId)
        					->toOptionHash();
        	$this->_depends[$id] = $category;
        }
        
        
        return Mage::helper('core')->jsonEncode($this->_depends);
    }
}
