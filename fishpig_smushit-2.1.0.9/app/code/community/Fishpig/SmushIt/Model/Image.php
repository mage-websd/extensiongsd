<?php
/**
 * @category Fishpig
 * @package Fishpig_Smushit
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Smushit_Model_Image extends Mage_Core_Model_Abstract
{
	/**
	 * Construct the resource model
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('smushit/image');
	}
}
