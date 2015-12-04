<?php
/**
 * @category	Fishpig
 * @package		Fishpig_Opti
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/magento-optimisation.html
 */

class Fishpig_Opti_Helper_Minify_Abstract extends Mage_Core_Helper_Abstract
{
	/**
	 * Determine whether the refresh parameter is present
	 * This triggers a refresh of all cached CSS/JS files
	 *
	 * @return bool
	 */
	protected function _isRefresh()
	{
		return Mage::app()->getRequest()->getParam('___refresh') === 'opti'
			|| Fishpig_Opti_Helper_Data::DEBUG === true;
	}
	
	/**
	 * Get the store ID and apply padding
	 *
	 * @return string
	 */
	protected function _getStoreId()
	{
		return str_pad(Mage::app()->getStore()->getId(), 4, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Include a minification library class file
	 *
	 * @param string $class
	 * @return bool
	 */
	protected function _includeLibrary($class)
	{
		if (!defined('COMPILER_INCLUDE_PATH')) {
			$includePath = get_include_path();
			set_include_path(Mage::getModuleDir('', 'Fishpig_Opti') . DS . 'Lib');
			
			$result = class_exists($class);
			set_include_path($includePath);
		}
		else {
			@include_once(COMPILER_INCLUDE_PATH . DS . 'Fishpig_Opti_Lib_' . $class . '.php');

			$result = class_exists($class);
		}

		return $result;
	}
}
