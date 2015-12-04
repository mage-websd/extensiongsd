<?php
/**
 * @category Fishpig
 * @package Fishpig_Smushit
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */

	$file = Mage::getBaseDir() . DS . implode(DS, array('app', 'code', 'core', 'Mage', 'Core', 'Model', 'Resource', 'Db', 'Abstract.php'));
	
	if (is_file($file)) {
		abstract class Fishpig_Smushit_Model_Resource_Image_Hack extends Mage_Core_Model_Resource_Db_Abstract {}
	}
	else {
		abstract class Fishpig_Smushit_Model_Resource_Image_Hack extends Mage_Core_Model_Mysql4_Abstract {}
	}

class Fishpig_Smushit_Model_Resource_Image extends Fishpig_Smushit_Model_Resource_Image_Hack
{
	/**
	 * Construct the resource model
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('smushit/image', 'image_id');
	}

	/**
	 * Construct the resource model
	 *
	 * @return void
	 */	
	public function installDatabaseTables()
	{
		$this->_getWriteAdapter()
			->query("
				CREATE TABLE IF NOT EXISTS " . $this->getMainTable() . " (
					`image_id` int(11) unsigned NOT NULL auto_increment,
					`file` varchar(255) NOT NULL default '',
					`src_size` int(9) NOT NULL default 0,
					`dest_size` int(9) NOT NULL default 0,
					`percent` int(3) NOT NULL default 0,
					`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`image_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Smush.it: Image Results';
			");
		
		return $this;
	}
	
	/**
	 * Create an entry in the DB using the file and API result
	 *
	 * @param string $file
	 * @param array $result
	 * @return Fishpig_SmushIt_Model_Image|false
	 */
	public function createUsingResult($file, array $result)
	{
		$data = array(
			'file' => substr($file, strlen(Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . 'cache' . DS)),
			'src_size' => $result['src_size'],
			'dest_size' => $result['dest_size'],
			'percent' => $result['percent'],
			'created_at' => now(),
		);
		
		// Delete any existing entries for this image
		$this->_getWriteAdapter()
			->delete(
				$this->getMainTable(),
				$this->_getWriteAdapter()->quoteInto('file=?', $data['file'])
			);
		
		// Add the new entry for this image	
		$this->_getWriteAdapter()
			->insert(
				$this->getMainTable(),
				$data
			);
			
		if ($lastInsertId = $this->_getWriteAdapter()->lastInsertId()) {
			$image = Mage::getModel('smushit/image')->load($lastInsertId);
			
			if ($image->getId()) {
				return $image;
			}
		}
		
		return false;
	}
}
