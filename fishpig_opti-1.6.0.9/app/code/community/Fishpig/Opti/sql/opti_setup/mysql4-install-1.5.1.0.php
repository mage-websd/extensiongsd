<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Opti
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

	$this->startSetup();

	$configs = array(
		'opti/minify/css' => 'opti/css/minify',
		'opti/minify/html' => 'opti/html/minify',
		'opti/minify/js' => 'opti/js/minify',
		'opti/minify/allowed_modules' => 'opti/conditions/modules',
	);

	$table = $this->getTable('core/config_data');
	$db = $this->getConnection();
	
	foreach($configs as $from => $to) {
		try {
			$db->update($table, array('path' => $to), $db->quoteInto('path=?', $from));
		}
		catch (Exception $e) {
			Mage::logException($e);
		}
	}

	$this->endSetup();
