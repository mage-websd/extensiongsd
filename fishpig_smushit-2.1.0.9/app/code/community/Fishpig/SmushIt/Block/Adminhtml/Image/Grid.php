<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SmushIt
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_SmushIt_Block_Adminhtml_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Set the grid block options
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		
		Mage::getResourceSingleton('smushit/image')->installDatabaseTables();
		
		$this->setId('smushit_grid');
		$this->setDefaultSort('savings');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
	}

	/**
	 * Initialise and set the collection for the grid
	 *
	 */
	protected function _prepareCollection()
	{
		$this->setCollection(Mage::getResourceModel('smushit/image_collection'));

		return parent::_prepareCollection();
	}
    
	/**
	 * Add the columns to the grid
	 *
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('file', array(
			'header'	=> $this->__('Image'),
			'align'		=> 'left',
			'index'		=> 'file',
		));
		
		$this->addColumn('src_size', array(
			'header'	=> $this->__('Source Size (bytes)'),
			'align'		=> 'left',
			'index'		=> 'src_size',
			'type' => 'number',
		));
		
		$this->addColumn('dest_size', array(
			'header'	=> $this->__('Result Size (bytes)'),
			'align'		=> 'left',
			'index'		=> 'dest_size',
			'type' => 'number',
		));
		
		$this->addColumn('percent', array(
			'header'	=> $this->__('Compression Percentage (%)'),
			'align'		=> 'left',
			'index'		=> 'percent',
			'type' => 'number',
		));

		$this->addColumn('created_at', array(
			'header' => Mage::helper('cms')->__('Date'),
			'index' => 'created_at',
			'type' => 'datetime',
			'align' => 'right',
		));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('image_id');
		$this->getMassactionBlock()->setFormFieldName('image');
	
		$this->getMassactionBlock()->addItem('smush', array(
			'label'=> $this->__('Smush Images'),
			'url'  => $this->getUrl('*/*/massSmush'),
		));
	}
	
	/**
	 * Retrieve the URL for the row
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function getRowUrl($row)
	{
		return null;
	}
}
