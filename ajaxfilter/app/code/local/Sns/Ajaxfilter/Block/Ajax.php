<?php 

class Sns_Ajaxfilter_Block_Ajax extends Mage_Core_Block_Template{
	public function __construct(){

		$this->config = Mage::getStoreConfig('ajaxfilter_cfg');
		$this->url = Mage::getStoreConfig('web/unsecure/base_url');

		$this->isEnabled = $this->config['general']['isenabled'];
		$this->ajaxPrice = $this->config['ajax_conf']['price'];
		$this->ajaxLayered = $this->config['ajax_conf']['layered'];
		$this->ajaxToolbar = $this->config['ajax_conf']['toolbar'];
		$this->loadingText = $this->config['ajax_conf']['loading_text'];
		$this->loadingImage = $this->getSkinUrl('sns/ajaxfilter/images/ajax-loader1.gif');
	}
}