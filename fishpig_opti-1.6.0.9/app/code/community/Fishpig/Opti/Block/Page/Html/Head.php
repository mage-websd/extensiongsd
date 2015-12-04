<?php

class Fishpig_Opti_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
	protected $_debug = false;
	
	protected function _toHtml()
	{
		return ($this->_debug ? '<!-- ' . $this->getNameInLayout() . ' -->' : '')
			. $this->getCssJsHtml()
			. ($this->_debug ? '<!-- /' . $this->getNameInLayout() . ' -->' : '')
	}
}
