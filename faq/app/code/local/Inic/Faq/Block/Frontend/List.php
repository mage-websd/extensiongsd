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
class Inic_Faq_Block_Frontend_List extends Mage_Core_Block_Template
{
	protected $_faqCollection;
	protected $_faqfrequentCollection;

	protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle($this->htmlEscape($this->__('Frequently Asked Questions')) . ' - ' . $head->getTitle());
        }
    }

	/**
	 * Returns collection of current FAQ entries
	 *
	 * @param int $pageSize
	 * @return Inic_Faq_Model_Mysql_Faq_Collection collection of current FAQ entries
	 */
	public function getFaqCollection($pageSize = null)
	{
		if (!$this->_faqCollection || (intval($pageSize) > 0
			&& $this->_faqCollection->getSize() != intval($pageSize))
		) {
			$this->_faqCollection = Mage :: getModel('faq/faq')
				->getCollection()
				->addStoreFilter(Mage :: app()->getStore())
				->addIsActiveFilter()
				->addPositionSort();

			if (isset($pageSize) && intval($pageSize) && intval($pageSize) > 0) {
				$this->_faqCollection->setPageSize(intval($pageSize));
			}
		}

		return $this->_faqCollection;
	}

	/**
	 * Returns collection of Frequent FAQ entries
	 *
	 * @param int $pageSize
	 * @return Inic_Faq_Model_Mysql_Faq_Collection collection of Frequent FAQ entries
	 */
	public function getFrequentFaqCollection($pageSize = null)
	{
		$this->_faqfrequentCollection = Mage :: getModel('faq/faq');
		if(Mage::getStoreConfig('faq_section/general/no_most_frequent_que')){
			$pageSize = Mage::getStoreConfig('faq_section/general/no_most_frequent_que');
		}
		if (Mage::getStoreConfig('faq_section/general/frequent_enable') && !$this->_faqfrequentCollection || (intval($pageSize) > 0 && $this->_faqfrequentCollection->getSize() != intval($pageSize))
		) {
			$this->_faqfrequentCollection = Mage :: getModel('faq/faq')
				->getCollection()
				->addStoreFilter(Mage :: app()->getStore())
				->addFieldToFilter('is_most_frequent',array('eq'=>1))
				->addIsActiveFilter();

			if (isset($pageSize) && intval($pageSize) && intval($pageSize) > 0) {
				$this->_faqfrequentCollection->setPageSize(intval($pageSize));
			}
		}

		return $this->_faqfrequentCollection;
	}

	/**
	 * Returns all active categories
	 *
	 * @return Inic_Faq_Model_Mysql4_Category_Collection
	 */
	public function getCategoryCollection()
	{
	    $categories = $this->getData('category_collection');
	    if (is_null($categories)) {
    	    $categories =  Mage::getResourceSingleton('faq/category_collection')
    	       ->addStoreFilter(Mage::app()->getStore())
    	       ->addIsActiveFilter()
						 ->addPositionSort();
    	    $this->setData('category_collection', $categories);
	    }
	    return $categories;
	}

	/**
	 * Returns the item collection for the given category
	 *
	 * @param Inic_Faq_Model_Category $category
	 * @return Inic_Faq_Model_Mysql4_Faq_Collection
	 */
	public function getItemCollectionByCategory(Inic_Faq_Model_Category $category)
	{
		$pageSize=null;
		if(Mage::getStoreConfig('faq_section/general/no_of_cat_que')){
			$pageSize = Mage::getStoreConfig('faq_section/general/no_of_cat_que');
		}
		$catQuestions=$category->getItemCollection()->addIsActiveFilter()->addStoreFilter(Mage::app()->getStore());
		if (isset($pageSize) && intval($pageSize) && intval($pageSize) > 0) {
				$catQuestions->setPageSize(intval($pageSize));
			}
	    return $catQuestions;
	}

	public function getCategoryUrl(Inic_Faq_Model_Category $category){
		return $this->getUrl('faq/index/categoryshow',array('_secure'=>true,'cat_id'=>$category->getId()));
	}

	/**
	 * Returns the item collection count for the given category
	 *
	 * @param Inic_Faq_Model_Category $category
	 * @return int
	 */
	public function getItemCollectionByCategoryCount(Inic_Faq_Model_Category $category){
		$category->setData('item_collection',null);
		$catQuestionsCollection=$category->getItemCollection()->addIsActiveFilter()->addStoreFilter(Mage::app()->getStore());
		return (count($catQuestionsCollection) > Mage::getStoreConfig('faq_section/general/no_of_cat_que')) ? true:false;
	}

	/**
	 * Simple helper function to determine, whether there are FAQ entries or not.
	 *
	 * @return boolean True, if FAQ are given.
	 */
	public function hasFaq()
	{
		return $this->getFaqCollection()->getSize() > 0;
	}

	/**
	 * Simple helper function to determine, whether there are Frequent FAQ entries or not.
	 * And the backend Configuration is set to yes or not
	 * @return boolean
	 */
	public function hasFrequent()
	{
		$size=0;
		$flg=false;
		if(isset($this->_faqfrequentCollection)){
			$size=$this->_faqfrequentCollection->getSize();
		}else{
			$size=$this->getFrequentFaqCollection()->getSize();
		}
		if(Mage::getStoreConfig('faq_section/general/frequent_enable') && $size > 0)
		{
			$flg=true;
		}

		return $flg;
	}

	public function getIntro($faqItem)
	{
		$_intro = strip_tags($faqItem->getContent());
		$_intro = mb_substr($_intro, 0, mb_strpos($_intro, "\n"));

		$length = 100 - mb_strlen($faqItem->getQuestion());
		if ($length < 0) {
			return '';
		}
		if (mb_strlen($_intro) > $length) {
			$_intro = mb_substr($_intro, 0, $length);
			$_intro = mb_substr($_intro, 0, mb_strrpos($_intro, ' ')).'...';
		}

		return $_intro;
	}

}
