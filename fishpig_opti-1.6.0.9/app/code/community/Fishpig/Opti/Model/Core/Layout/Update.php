<?php
/**
 * @category	Fishpig
 * @package Fishpig_Opti
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <ben@fishpig.co.uk>
 * @info http://fishpig.co.uk/magento/extensions/minify/
 */
	
class Fishpig_Opti_Model_Core_Layout_Update extends Mage_Core_Model_Layout_Update
{
	/**
	 * Methods used for merging
	 *
	 * @var array
	 */
	protected $_methods = array(
		'addJs' => 'js',
		'addCss' => 'skin_css',
	);
	
	/**
	 * Handles to ignore
	 *
	 * @var array
	 */
	protected $_ignoredHandles = array(
		'default',
		'print',
	);
	
	/**
	 * Cleverly merge the JS and CSS files for the frontend
	 *
	 * @param string $area
	 * @param string $package
	 * @param string $theme
	 * @param int $storeId = null
	 * @return Mage_Core_Model_Layout_Element
	 */
	public function getFileLayoutUpdatesXml($area, $package, $theme, $storeId = null)
	{
		$xml = parent::getFileLayoutUpdatesXml($area, $package, $theme, $storeId);
		$new = array();
		
		foreach($this->_methods as $method => $type) {
			$defaults = $this->_getDefaultItems($xml, $method);

			foreach($xml->children() as $handle => $node) {
				if (in_array($handle, $this->_ignoredHandles)) {
					continue;
				}
		
				if ($actions = $node->xpath(".//action[@method='".$method."']")) {
					$remove = array();
					$add = array();
					
					foreach($actions as $i => $action) {
						list($attributes, $file) = array_values($action->asArray());
						
						if (!in_array($file, $defaults)) {
							$remove[$file] = sprintf('<action method="removeItem"><type>%s</type><file>%s</file></action>', $type, $file);
							$add[$file] = sprintf('<action method="%s"><file>%s</file></action>', $method, $file);
						}
					}
					
					if (count($remove) > 0 && count($add) > 0) {
						$new[] = sprintf(
							'<%s><reference name="head">%s<block type="opti/page_html_head" name="head.%s">%s</block></reference></%s>', 
							$handle, 
							implode('', $remove), 
							$handle, 
							implode('', $add), 
							$handle
						);
					}
				}
			}
		}

		#echo implode('', $new);exit;

		return simplexml_load_string(
			'<layouts>' . $xml->innerXml() . (!$new ? implode('', $new) : '') . '</layouts>',
			$this->getElementClass()
		);
	}
	
	/**
	 * Get the default items
	 *
	 * @param Mage_Core_Model_Layout_Element $xml
	 * @param string $method
	 * @return array
	 */
	protected function _getDefaultItems($xml, $method)
	{
		$defaults = array();
		
		foreach($xml->children() as $handle => $node) {
			if ($handle !== 'default') {
				continue;
			}
			
			if ($actions = $node->xpath(".//action[@method='".$method."']")) {
				foreach($actions as $action) {
					$defaults[] = (string)$action->script;
				}	
			}
		}
			
		return array_unique($defaults);
	}
}
