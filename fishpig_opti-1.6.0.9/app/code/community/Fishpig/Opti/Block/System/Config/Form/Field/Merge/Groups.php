<?php
/**
 * @category  Fishpig
 * @package  Fishpig_Opti
 * @license    http://fishpig.co.uk/license.txt
 * @author    Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Opti_Block_System_Config_Form_Field_Merge_Groups extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	/**
	 * Prepare to render
	*/
	protected function _prepareToRender()
	{
		$this->addColumn('group', array(
			'label' => $this->__('Group'),
			'style' => 'max-width: 140px;',
			
		));
	
		$this->addColumn('type', array(
			'label' => $this->__('Type'),
			'style' => 'max-width: 70px;',
		));
		
		$this->addColumn('file', array(
			'label' => $this->__('File'),
		));
	
		$this->_addAfter = false;
		$this->_addButtonLabel = $this->__('Add New');
	}
}
