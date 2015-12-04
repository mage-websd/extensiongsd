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
class Inic_Faq_IndexController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Displays the FAQ list.
	 */
	public function indexAction()
	{
		$this->loadLayout()->renderLayout();
	}
	
	/**
	 * Displays the current FAQ's detail view
	 */
	public function showAction()
	{
		$this->loadLayout()->renderLayout();
	}
	
	
	/**
	 * Displays the current Category's FAQ list view
	 */
	public function categoryshowAction()
	{
		$this->loadLayout()->renderLayout();
	}
	
	/**
	 * Displays the current Category's FAQ list view
	 */
	public function resultAction()
	{
		$this->loadLayout()->renderLayout();
	}
}
