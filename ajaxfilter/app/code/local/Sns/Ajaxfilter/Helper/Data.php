<?php
class Sns_Ajaxfilter_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getJQquery(){
		if (Mage::getStoreConfigFlag('ajaxfilter_cfg/advanced/include_jquery') && Mage::getStoreConfigFlag('ajaxfilter_cfg/general/isenabled')){
			if (null == Mage::registry('sns.jquery')){
				Mage::register('sns.jquery', 1);
				return 'sns/ajaxfilter/js/jquery-1.7.2.min.js';
			}
		}
		return;
	}
	public function getJQqueryUI(){
		if (Mage::getStoreConfigFlag('ajaxfilter_cfg/advanced/include_jqueryui') && Mage::getStoreConfigFlag('ajaxfilter_cfg/general/isenabled')){
			if (null == Mage::registry('sns.jqueryui')){
				Mage::register('sns.jqueryui', 1);
				return 'sns/ajaxfilter/js/jquery-ui.min.js';
			}
		}
		return;
	}
	public function getJQqueryNoconflict(){
		if (Mage::getStoreConfigFlag('ajaxfilter_cfg/advanced/include_jquery')){
			if (null == Mage::registry('sns.jquerynoconflict')){
				Mage::register('sns.jquerynoconflict', 1);
				return 'sns/ajaxfilter/js/sns.noconflict.js';
			}
		}
		return;
	}

}
