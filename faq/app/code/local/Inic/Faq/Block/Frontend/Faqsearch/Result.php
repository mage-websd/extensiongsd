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
class Inic_Faq_Block_Frontend_Faqsearch_Result extends Mage_Core_Block_Template {
	
	protected $_search;
	
	protected function _prepareLayout()
    {
        $search = $this->getSearch();
        $keyword=$this->getRequest()->getParam('keyword');
        if ($search !== false && $head = $this->getLayout()->getBlock('head')) {
            $head->setTitle('Search Result For : '.$this->htmlEscape($keyword) . ' - ' . $head->getTitle());
        }
    }
	
    /**
	 * Function to gather the searched terms in questions
	 *
	 * @return Inic_Faq_Model_Faq Collection
	 */
    public function getSearch() {
    	if (!$this->_search) {
	    	$keyword=$this->getRequest()->getParam('keyword');
			$this->_search = Mage :: getModel('faq/faq')->getCollection();
			if($this->getRequest()->getParam('cat_id')){
	    		$id=$this->getRequest()->getParam('cat_id');
	    		$category = Mage :: getModel('faq/category')->load($id);
	    		$this->_search=$category->getItemCollection()->addIsActiveFilter()->addStoreFilter(Mage::app()->getStore());
	    	}
	    	if($keyword!=""){
	    	$this->_search->getSelect()->where("(question LIKE '%".$keyword."%') OR (answer LIKE '%".$keyword."%')");
	    	}
    	}
		return $this->_search;
    }
}