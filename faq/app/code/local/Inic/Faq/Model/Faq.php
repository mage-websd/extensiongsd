<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013 Indianic
 */

/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @author     Indianic 
 */
class Inic_Faq_Model_Faq extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('faq/faq');
    }
}
