<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2009 Indianic
 */

/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @author     Inic
 */
class Inic_Faq_Block_Frontend_Category extends Mage_Core_Block_Template {
	
	protected $_catfaq;
	protected $_catfaqcCollection;
	
	
	protected function _prepareLayout()
    {
        $categoryfaq = $this->getCategory();
        if ($categoryfaq !== false && $head = $this->getLayout()->getBlock('head')) {
            $head->setTitle($this->htmlEscape($categoryfaq->getName()) . ' - ' . $head->getTitle());
        }
    }
	
    public function getCategory() {
    	if (!$this->_catfaq) {
	    	$id = intval($this->getRequest()->getParam('cat_id'));
	    	try {
					$this->_catfaq = Mage :: getModel('faq/category')->load($id); 
					
					if ($this->_catfaq->getIsActive() != 1){
						Mage::throwException('Catagory is not active');
					}
			}catch (Exception $e) {
					$this->_catfaq = false;
			}
    	}
		return $this->_catfaq;
    }
    
	/**
	 * Function to gather the current faq item
	 *
	 * @return Inic_Faq_Model_Faq The current faq item
	 */
	public function getcatFaqCollection() {
		try{
			if (!$this->_catfaq) {
					Mage::throwException('Please Select Category');
			}else{
				$this->_catfaqcCollection=$this->_catfaq->getItemCollection()->addIsActiveFilter()->addStoreFilter(Mage::app()->getStore());
			}
		}catch (Exception $e) {
				$this->_catfaqcCollection = false;
		}
		return $this->_catfaqcCollection;
	}
}