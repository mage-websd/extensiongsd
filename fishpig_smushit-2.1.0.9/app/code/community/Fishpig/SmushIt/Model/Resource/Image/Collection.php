<?php
/**
 * @category Fishpig
 * @package Fishpig_Smushit
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */


	$file = Mage::getBaseDir() . DS . implode(DS, array('app', 'code', 'core', 'Mage', 'Core', 'Model', 'Resource', 'Db', 'Abstract.php'));
	
	if (is_file($file)) {
		abstract class Fishpig_Smushit_Model_Resource_Image_Collection_Hack extends Mage_Core_Model_Resource_Db_Collection_Abstract {}
	}
	else {
		abstract class Fishpig_Smushit_Model_Resource_Image_Collection_Hack extends Mage_Core_Model_Mysql4_Collection_Abstract {}
	}

class Fishpig_Smushit_Model_Resource_Image_Collection extends Fishpig_Smushit_Model_Resource_Image_Collection_Hack
{
	/**
	 * Set the resource
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('smushit/image');
	}
}
