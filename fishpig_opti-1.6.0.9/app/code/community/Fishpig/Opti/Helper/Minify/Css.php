<?php
/**
 * @category	Fishpig
 * @package		Fishpig_Opti
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/magento-optimisation.html
 */

class Fishpig_Opti_Helper_Minify_Css extends Fishpig_Opti_Helper_Minify_Abstract
{
	/**
	 * Minify the CSS files in the head
	 *
	 * @param Varien_Event_Observer $observer)
	 * @return $this
	 */	
	public function minifyHeadItems(array $items)
	{
		$cssDir = Mage::getBaseDir('media') 
			. DS . (Mage::app()->getStore()->isCurrentlySecure() ? 'css_secure' : 'css');
		
		if (!is_dir($cssDir)) {
			@mkdir($cssDir);
			
			if (!is_dir($cssDir)) {
				return $items;
			}
		}

		$refresh = $this->_isRefresh();
		$storeId = $this->_getStoreId();
		$_design = Mage::getDesign();

		foreach($items as $key => $item) {
			if ($item['if'] || $item['type'] !== 'skin_css') {
				continue;
			}

			$originalUrl = $_design->getSkinUrl($item['name']);
			$originalFile = $_design->getFilename($item['name'], array('_type' => 'skin'));
			
			
			$minifiedFile =  $cssDir . DS . 'opti-' . md5($storeId . '-' . filemtime($originalFile) . '-' . str_replace(DS, '-', substr($originalFile, strlen(Mage::getBaseDir('skin'))+1))) . '.css';

			$item['name'] = '../../../../media/' . basename($cssDir) . '/' . basename($minifiedFile);

			if (!$refresh && is_file($minifiedFile)) {
				$items[$key] = $item;
			}
			else if (($css = @file_get_contents($originalFile)) !== false) {
				if (($css = $this->minify($css, dirname($originalUrl) . '/')) !== '') {
					if (@file_put_contents($minifiedFile, $css) && is_file($minifiedFile)) {
						$items[$key] = $item;
					}
				}
			}
		}

		return $items;
	}
	
	/**
	 * Minify hardcoded CSS files
	 *
	 * @param string $html
	 * @return string
	 */
	public function minifyHardcodedItems($html)
	{
		if (!preg_match_all('/(<link[^>]{0,}href=\"([^\"]{1,}.css)\".*\/>)/U', $html, $matches)) {
			return $html;
		}
		
		$cssFiles = array_combine($matches[2], $matches[1]);

		$baseSkinUrl = Mage::getBaseUrl('skin');
		$baseSkinDir = Mage::getBaseDir('skin') . DS;
		$targetDir = Mage::getBaseDir('media') . DS . (Mage::app()->getStore()->isCurrentlySecure() ? 'css_secure' : 'css') . DS;
		$targetUrl = Mage::getBaseUrl('media') . (Mage::app()->getStore()->isCurrentlySecure() ? 'css_secure' : 'css') . '/';

		foreach($cssFiles as $cssFile => $data) {
			if (strpos($cssFile, 'opti-') !== false) {
				continue;
			}

			if (strpos($cssFile, $baseSkinUrl) !== 0 && substr($cssFile, 0, 1) !== '/') {
				continue;
			}
			
			$fullCssFile = $cssFile;
			
			if (substr($cssFile, 0, 1) === '/') {
				$localFile = MAGENTO_ROOT . DS . ltrim($cssFile, '/');
				$localFilename = basename($localFile);
				$minifiedLocalFilename = md5($cssFile) . '.css';
			}
			else {
				$localFilename = substr($fullCssFile, strlen($baseSkinUrl));
				$localFile = $baseSkinDir . $localFilename;
				$minifiedLocalFilename = str_replace(DS, '-', $localFilename);
			}

			if (!is_file($localFile)) {
				continue;
			}
				
			if (!is_file($targetDir . $minifiedLocalFilename) && ($cssContent = file_get_contents($localFile))) {
				$before = strlen($cssContent);
				$cssContent = $this->minify($cssContent, dirname($cssFile));
				

				if (strlen($cssContent) >= $before) {
					continue;
				}

				@file_put_contents($targetDir . $minifiedLocalFilename, $cssContent);
				
				if (!is_file($targetDir . $minifiedLocalFilename)) {
					continue;
				}
			}
			
			$html = str_replace($data, str_replace($cssFile, $targetUrl . $minifiedLocalFilename, $data), $html);
		}

		return $html;
	}
	
	/**
	 * Minify inline CSS code
	 *
	 * @param array $css
	 * @return array
	 */
	public function minifyInlineCss(array $css)
	{
		foreach($css as $key => $value) {
			if (preg_match('/^(<style[^>]{0,}>)(.*)(<\/style>)$/sU', $value, $match)) {
				if ($value = $this->minify($match[2])) {
					$css[$key] = $match[1] . $value . $match[3];
				}
			}
		}

		return $css;
	}
	
	/**
	 * Minify the CSS string
	 *
	 * @param string $css
	 * @param string $baseUrl
	 * @return string
	 */
	public function minify($css, $baseUrl = null)
	{
		if ($this->_includeLibrary('CSSmin')) {
			$lib = new  CSSmin(true);
			
			$css = $lib->run($css);
		}
		
		if (!is_null($baseUrl)) {
			$baseUrl = rtrim($baseUrl, '/') . '/';
	
			if (preg_match_all('/url\((.*)\)/iU', $css, $matches)) {
				foreach($matches[0] as $it => $find) {
					$url = trim($matches[1][$it], "'\"");

					if (strpos($url, 'data:') === 0) {
						// Ignore data URIs
					}
					else if (strpos($url, 'http://') === false && strpos($url, 'https://') === false && substr($url, 0, 1) !== '/') {
						$url = $baseUrl . trim($url, '" \'/');
	
						$css = str_replace($find, "url('{$url}')", $css);
					}
				}
			}
		}

		return trim($css);
	}
}
