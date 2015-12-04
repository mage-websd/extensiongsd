<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SmushIt
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_SmushIt_Block_Adminhtml_Image extends Mage_Adminhtml_Block_Widget_Tabs
{
	/**
	 * Set the block options
	 *
	 * @return void
	 */
	public function __construct()
	{	
		parent::__construct();

		$this->setId('smushit_tabs');
        $this->setDestElementId('smushit_tabs_content');
		$this->setTitle($this->__('Smush.it') );
		$this->setTemplate('widget/tabshoriz.phtml');
	}
	
	protected function _prepareLayout()
	{
		$_layout = $this->getLayout();
		
		$this->addTab('image', array(
			'label'     => Mage::helper('catalog')->__('Images'),
			'content'   => $_layout->createBlock('smushit/adminhtml_image_grid')->toHtml(),
			'active'    => true,
		));

		if ($extend = $_layout->createBlock('smushit/adminhtml_extend')) {
			$extend->setNameInLayout('fishpig.extend')
				->setTabLabel($this->__('Add-Ons'))
				->setTabUrl('*/*/extend');
				
			$this->addTab('extend', $extend);
		}
				
		return parent::_prepareLayout();
	}
}
