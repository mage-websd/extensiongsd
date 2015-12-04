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
class Inic_Faq_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns config data
     * 
     * @param string $field Requested field
     * @return array config Configuration information
     */
    public function getConfigData($field)
    {
        $path = 'faq/config/' . $field;
        $config = Mage::getStoreConfig($path, Mage::app()->getStore());
        return $config;
    }
    
    /**
	 * Returns the class on the basis of backend layout settings
	 * @return varchar
	 */
	public function getQuestionView(){
		return (Mage::getStoreConfig('faq_section/general/view_style')==1 ? 'grid' : 'list');
	}
	
	/**
	 * Returns the parsed wyswing editor directives
	 * @return string
	 */
	public function filter($content){
		return Mage::helper('cms')->getBlockTemplateProcessor()->filter($content);
	}
}
