<?php
/**
 * @category Fishpig
 * @package Fishpig_SmushIt
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <ben@fishpig.co.uk>
 */

 	define('IS_COMMAND_LINE', PHP_SAPI === 'cli');
 	define('LB', IS_COMMAND_LINE ? "\n" : '<br/>');
 	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);	
	
	set_time_limit(0);
	
	$mageFile = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
	
	if (!is_file($mageFile)) {
		echo "Unable to load Mage.php" . LB;
		exit;
	}
	
 	@require($mageFile);
 	umask(0);
 	Mage::app();

 	try {
	 	echo LB . "Running Smush.it by FishPig" . LB;
	 	
	 	Mage::helper('smushit')->run();
	 	
	 	echo "Smush.it Complete" . LB;
	 }
	 catch (Exception $e) {
		 echo LB . 'Exception: ' . $e->getMessage() . LB . LB;
	 }
  	