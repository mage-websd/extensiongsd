<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SmushIt
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */
 
class Fishpig_SmushIt_Model_Cron
{
	/**
	 * Update stock quantities.
	 * This is called via the Magento CRON
	 *
	 * @return $this
	 */
	public function runImageOptimisation()
	{
		Mage::helper('smushit')->run();
		
		return $this;
	}
}
