<?php
/**
 * @category	Fishpig
 * @package		Fishpig_Opti
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/magento-optimisation.html
 */

class Fishpig_Opti_Helper_Minify_Js extends Fishpig_Opti_Helper_Minify_Abstract
{
	/**
	 * Minify the JS files in $items
	 * $items is taken from the Head block
	 *
	 * @param array $items
	 * @return array
	 */
	public function minifyHeadItems(array $items)
	{
		$refresh = $this->_isRefresh();
		$storeId = $this->_getStoreId();
		
		$_design = Mage::getDesign();
		
		$jsDir = Mage::getBaseDir('media') . DS . 'js';

		$baseSkinDir = dirname(dirname($_design->getSkinBaseDir()));
		$baseSkinUrl = dirname(dirname($_design->getSkinBaseUrl()));

		foreach($items as $key => $item) {
			if ($item['if'] || ($item['type'] !== 'js' && $item['type'] !== 'skin_js')) {
				continue;
			}
			
			if (strpos($item['name'], '?') !== false) {
				continue;
			}

			if ($item['type'] === 'skin_js') {
				$localFile = $_design->getFilename($item['name'], array('_type' => 'skin'));
				$minifiedFile = Mage::getBaseDir('media')  . DS . 'js' . DS . 'opti-' . md5(filemtime($localFile) . $storeId . '-' . str_replace(DS, '-', substr($localFile, strlen(Mage::getBaseDir('skin'))+1))) . '.js';
				$relativeFile = str_repeat('../', 4) . 'media/js/' . basename($minifiedFile);
			}
			else {
				$localFile = Mage::getBaseDir() . DS . 'js' . DS . $item['name'];
				$minifiedFile = $jsDir . DS . 'opti-' . md5(filemtime($localFile) . str_replace('/', '-', $item['name'])) . '.js';
				$relativeFile = '../media/js/' . basename($minifiedFile);
			}

			$item['name'] = $relativeFile;

			if (!$refresh && is_file($minifiedFile)) {
				$items[$key] = $item;
			}
			else if (($js = @file_get_contents($localFile)) !== false) {
				if (($js = $this->minify($js, true)) !== '') {
					if (@file_put_contents($minifiedFile, $js) && is_file($minifiedFile)) {
						$items[$key] = $item;
					}
				}
			}
		}

		return $items;	
	}

	/**
	 * Minify the JavaScript string
	 *
	 * @param string $js
	 * @return string
	 */
	public function minify($js, $external = false, $cdata = null)
	{
		if ($external === null) {
			$external = false;
		}
		
		if (trim($js) === '') {
			return '';
		}

		if ($cdata === null) {
			$cdata = !$external && (strpos($js, '&') !== false || strpos($js, '<') !== false);
		}

		if (!$this->_includeLibrary('JSMin')) {
			return $js;
		}
		
		try {
			$js = JSMin::minify($js);
		}
		catch (Exception $e) {}

		return $cdata ? "//<![CDATA[\n" . $js . "//]]>" : $js;
	}

	/**
	 * Merge the head JS items
	 *
	 * @param array $items
	 * @return array
	 */
	public function mergeHeadItems(array $items)
	{
		$groups = $this->_getMergeGroups();

		if (count($groups) === 0) {
			return $items;
		}
		
		$storeId = $this->_getStoreId();
		$refresh = $this->_isRefresh();
		$finalMerge = array();
		
		foreach($groups as $group => $files) {
			if (count($files) <= 1) {
				continue;
			}

			$mergedFile = Mage::getBaseDir('media') . DS . 'js' . DS . 'opti-' . $storeId . '-merged-' . $group . '.js';
			$mergedFileExists = is_file($mergedFile);
			$mergeKey = 'js/' . basename($mergedFile);
			$data = '';

			foreach($files as $key => $file) {
				if (isset($items[(string)$file])) {
					$item = $items[(string)$file];
					unset($items[(string)$file]);
					
					if ($refresh || !$mergedFileExists) {
						if ($item['type'] === 'js') {
							$originalFile = realpath(Mage::getBaseDir() . DS . 'js' . DS . $item['name']);
						}
						else if ($item['type'] === 'skin_js') {
							$originalFile = realpath(Mage::getDesign()->getSkinBaseDir() . DS . $item['name']);
						}
						else {
							continue;
						}
						
						$data .= "\n" . file_get_contents($originalFile);
					}
				}
			}
			
			if ($refresh || !$mergedFileExists && trim($data)) {
				@file_put_contents($mergedFile, trim($data));
			}
				
			$finalMerge[$mergeKey] = array(
				'type' => 'js',
				'name' => '../media/' . $mergeKey,
				'params' => '',
				'if' => '',
				'cond' => '',
			);
		}
		
		$items = array_merge($finalMerge, $items);

		return $items;
	}
	
	/**
	 * Get all of the merge groups
	 *
	 * @return array
	 */
	protected function _getMergeGroups()
	{
		$groups = json_decode(json_encode(Mage::getConfig()->getNode('opti/js/merge/groups')), true);

		if ($cgroupstring = Mage::getStoreConfig('opti/js/merge_groups')) {
			$cgroups = (array)unserialize($cgroupstring);

			foreach($cgroups as $cgroup) {
				if (!isset($groups[$cgroup['group']])) {
					$groups[$cgroup['group']] = array();
				}

				$key = str_replace(array('/', '.js', '.min'), array('_', '', ''), $cgroup['file']);
				$key = $cgroup['file'];
				$groups[$cgroup['group']][$key] = $cgroup['type'] . '/' . $cgroup['file'];
			}
		}
		
		return $groups;
	}

	/**
	 * Loop through the scripts array and minify
	 *
	 * @param array $scripts
	 * @return array
	 */
	public function minifyScriptsArray(array $scripts)
	{
		foreach($scripts as $it => $script) {
			if (preg_match('/(<script.*>)(.*)(<\/script>)/iUs', $script, $match)) {
				list($full, $open, $body, $close) = array_values($match);
				
				if (trim($body) !== '') {
					$scripts[$it] = $open . $this->minify($body, null, false) . $close;
				}
			}
		}

		return $scripts;
	}
	
	/**
	 * Minify hardcoded JS files
	 *
	 * @param string $html
	 * @return string
	 */
	public function minifyHardcodedItems($html)
	{
		if (!preg_match_all('/(<script[^>]{1,}src=\"([^\"]{1,})\"[^>]{0,}><\/script>)/U', $html, $matches)) {
			return $html;
		}
		
		$jsFiles = array_combine($matches[2], $matches[1]);
		
		$baseSkinUrl = Mage::getBaseUrl('skin');
		$baseJsUrl = Mage::getBaseUrl('js');
		$baseSkinDir = Mage::getBaseDir('skin') . DS;
		$baseJsDir = Mage::getBaseDir() . DS . 'js' . DS;
		$targetDir = Mage::getBaseDir('media') . DS . 'js' . DS;
		$targetUrl = Mage::getBaseUrl('media') . 'js/';

		foreach($jsFiles as $jsFile => $data) {
			if (strpos($jsFile, 'opti-') !== false) {
				continue;
			}
			
			if (strpos($jsFile, $baseJsUrl) === 0) {
				$localFilename = substr($jsFile, strlen($baseJsUrl));
				$localFile = $baseJsDir . $localFilename;
			}
			else if (strpos($jsFile, $baseSkinUrl) === 0) {
				$localFilename = substr($jsFile, strlen($baseSkinUrl));
				$localFile = $baseSkinDir . $localFilename;
			}
			else {
				continue;
			}
			
			if (!is_file($localFile)) {
				continue;
			}

			$minifiedLocalFilename = str_replace(DS, '-', $localFilename);

			if (!is_file($targetDir . $minifiedLocalFilename) && ($jsContent = file_get_contents($localFile))) {
				$before = strlen($jsContent);
				$jsContent = $this->minify($jsContent, true);
				
				if (strlen($jsContent) >= $before) {
					continue;
				}

				@file_put_contents($targetDir . $minifiedLocalFilename, $jsContent);
				
				if (!is_file($targetDir . $minifiedLocalFilename)) {
					continue;
				}
			}
			
			$html = str_replace($data, str_replace($jsFile, $targetUrl . $minifiedLocalFilename, $data), $html);
		}

		return $html;
	}
}
