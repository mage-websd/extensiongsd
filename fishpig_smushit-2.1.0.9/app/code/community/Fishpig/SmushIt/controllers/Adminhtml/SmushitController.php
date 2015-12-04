<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_SmushIt_Adminhtml_SmushitController extends Mage_Adminhtml_Controller_Action
{
	/**
	 *
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$this->_title('FishPig');
		$this->_title('Smush.it');
		$this->renderLayout();
	}

	/**
	 * Display the Extend tab
	 *
	 * @return void
	 */
	public function extendAction()
	{
		$block = $this->getLayout()
			->createBlock('smushit/adminhtml_extend')
			->setModule('Fishpig_SmushIt')
			->setTemplate('large.phtml')
			->setLimit(3)
			->setPreferred(array_flip(array('Fishpig_Bolt', 'Fishpig_NoBots', 'Fishpig_Opti')));

		$this->getResponse()->setBody($block->toHtml());
	}
	
	/**
	 * Determine ACL permissions
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return true;
	}	
}
