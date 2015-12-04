<?php
/**
 * @category Fishpig
 * @package Fishpig_Opti
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 * @info http://fishpig.co.uk/magento/extensions/minify/
 */

class Fishpig_Opti_Model_Observer extends Varien_Object
{


	public function mergeJsCssObserver(Varien_Event_Observer $observer)
	{
		return;
		$layout = $observer->getEvent()->getLayout();
		$design = Mage::getSingleton('core/design_package');
        $cacheKey = 'LAYOUT_' . $design->getArea() . '_STORE' . Mage::app()->getStore()->getId() . '_' . $design->getPackageName() . '_' . $design->getTheme('layout');

		if (!Mage::app()->useCache('layout')) {
			return $this;	
		}
	
		if (!($xml = Mage::app()->loadCache($cacheKey))) {
			return $this;
		}
		
		$elementClass = $layout->getUpdate()->getElementClass();
		
		$xml = simplexml_load_string($xml, $elementClass);
		$defaultItems = array();
		
		foreach($this->_methods as $method) {
			foreach($xml->children() as $handle => $node) {
				if ($handle !== 'default') {
					continue;
				}
				
				if ($actions = $node->xpath(".//action[@method='".$method."']")) {
					foreach($actions as $action) {
						$defaultItems[] = (string)$action->script;
					}	
				}
			}
			
			$defaultItems = array_unique($defaultItems);

			foreach($xml->children() as $handle => $node) {
				if (in_array($handle, $this->_ignoredHandles)) {
					continue;
				}
		
				if ($actions = $node->xpath(".//action[@method='".$method."']")) {
					$files = array();							
					$newXml = array();
					
					foreach($actions as $i => $action) {
						list($attributes, $file) = array_values($action->asArray());
						
						if (in_array($file, $defaultItems)) {
							continue;
						}
						
						#print_r(get_class_methods($action));exit;
						$files[] = $file;
					}

					if (count($files) > 0) {
						$files = array_unique($files);
						
unset($actions);

						foreach($files as $file) {
							$newXml[] = sprintf('<action method="%s"><%s>%s</%s></action>', $method, 'script', $file, 'script');
						}
						
						$newXml = sprintf(
							'<block type="page/html_head" name="%s" template="opti/head.phtml">%s</block>',
							'head.' . $handle,
							implode("\n\t", $newXml)
						);
						
						$configNew = new Varien_Simplexml_Config();

						$configNew->loadString(
							sprintf('<layout><%s>%s</%s></layout>', $handle, $newXml, $handle)
						);
						
						$xml->extend($configNew);

					}
				}
			}

			Mage::app()->saveCache($xml->asXml(), $cacheKey, array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG), null);
		}
	}
	
	/**
	 * Minify the HTML currently in the HTML Body
	 *
	 * @param Varien_Event_Observer $observer)
	 * @return $this
	 */
	public function minifyHtmlObserver(Varien_Event_Observer $observer)
	{
		if (!Mage::helper('opti')->isEnabled()) {
			return $this;
		}
		
		if (!$this->isAllowedRoute()) {
			return $this;
		}

		$html = $observer
			->getEvent()
				->getFront()
					->getResponse()
						->getBody();

		if (Mage::getStoreConfigFlag('opti/html/minify')) {
			$html = Mage::helper('opti/minify_html')->minify($html);
		}
		
		$html = Mage::helper('opti/minify_html')->clean($html);
		
		if (Mage::getStoreConfigFlag('opti/js/minify_hardcoded')) {
			$html = Mage::helper('opti/minify_js')->minifyHardcodedItems($html);
		}
		
		if (Mage::getStoreConfigFlag('opti/css/minify_hardcoded')) {
			$html = Mage::helper('opti/minify_css')->minifyHardcodedItems($html);
		}
		
		$observer->getEvent()
			->getFront()
				->getResponse()
					->setBody($html);

		return $this;
	}

	/**
	 * Minify all items
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function minifyJsCssObserver(Varien_Event_Observer $observer)
	{
		if (!Mage::helper('opti')->isEnabled()) {
			return $this;
		}
		
		if (!$this->isAllowedRoute()) {
			return $this;
		}

		if ($blocks = (array)Mage::getConfig()->getNode('opti/source_blocks')) {
			$blocks = array_keys($blocks);
			
			foreach($blocks as $blockName) {				
				if (!($block = Mage::getSingleton('core/layout')->getBlock($blockName))) {
					continue;
				}

				if (!($items = $block->getItems())) {
					continue;
				}

				if (Mage::getStoreConfigFlag('opti/css/minify')) {
					$items = Mage::helper('opti/minify_css')->minifyHeadItems($items);
				}
				
				if (Mage::getStoreConfigFlag('opti/js/minify')) {
					$items = Mage::helper('opti/minify_js')->minifyHeadItems($items);
				}
				
				/*
				if (Mage::getStoreConfigFlag('opti/js/merge') && !Mage::getStoreConfigFlag('dev/js/merge_files')) {
					$items = Mage::helper('opti/minify_js')->mergeHeadItems($items);
				}
				*/
				
				$block->setItems($items);
			}
		}

		return $this;
	}
	
	/**
	 * Determine whether to run on the route
	 *
	 * @return bool
	 */
	public function isAllowedRoute()
	{
		foreach((array)Mage::app()->getResponse()->getHeaders() as $header) {
			if (isset($header['name']) && strtolower($header['name']) === 'content-type') {
				$isTextHtml = strpos($header['value'], 'text/html') !== false;
			}
		}

		if (!$isTextHtml) {
			return false;
		}

		$allowedModules = (array)explode(',', trim(Mage::getStoreConfig('opti/conditions/modules'), ','));

		return in_array(Mage::app()->getRequest()->getModuleName(), $allowedModules);
	}
}
