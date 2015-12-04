<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013
 */

/**
 * Category Model for FAQ Items
 *
 * @category   Inic
 * @package    Inic_Faq
 * @author     Inic 
 */
class Inic_Faq_Model_Category extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('faq/category');
    }
    
    public function getName()
    {
        return $this->getCategoryName();
    }
    
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            $collection = Mage::getSingleton('faq/faq')->getCollection()
                ->addCategoryFilter($this);
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }
}
