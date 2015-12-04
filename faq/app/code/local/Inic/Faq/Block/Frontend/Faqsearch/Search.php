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
class Inic_Faq_Block_Frontend_Faqsearch_Search extends Mage_Core_Block_Template {
	
    /**
	 * Function to return search result page url
	 *
	 * @return Search Url
	 */
    public function getSearchUrl() {
		return $this->getUrl('faq/index/result');
    }
    
    /**
	 * Function to check search is enabled or not
	 *
	 * @return bool;
	 */
    public function isSearchEnabled()
    {
    	return Mage::getStoreConfig('faq_section/general/search_enable') ? true : false;
    }
}