<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Giftcard
 * @version    1.0.8
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Giftcard_Block_Frontend_Catalog_Product_View_Type_Giftcard extends Mage_Catalog_Block_Product_View_Abstract
{
    public function getAmountOptions()
    {
        $result = array();
        foreach ($this->getProduct()->getAwGcAmounts() as $amount) {
            $result[] = Mage::app()->getStore()->roundPrice($amount['value']);
        }
        sort($result);
        return $result;
    }

    public function isAllowEmail()
    {
        if ($this->getProduct()->getTypeInstance()->isTypePhysical($this->getProduct())) {
            return false;
        }
        return true;
    }

    public function hasAmountOptions()
    {
        if (!$this->getProduct()->getAwGcAllowOpenAmount() && !$this->getProduct()->getAwGcAmounts()) {
            return false;
        }
        return true;
    }

    public function getCustomerName()
    {
        $firstName = (string)Mage::getSingleton('customer/session')->getCustomer()->getFirstname();
        $lastName  = (string)Mage::getSingleton('customer/session')->getCustomer()->getLastname();
        $_result = $firstName . ' ' . $lastName;
        return trim($_result);
    }

    public function getCustomerEmail()
    {
        return (string) Mage::getSingleton('customer/session')->getCustomer()->getEmail();
    }

    public function isAllowMessage()
    {
        if ($this->getProduct()->getAwGcConfigAllowMessage()) {
            return (bool) Mage::helper('aw_giftcard/config')->isAllowGiftMessage();
        }
        return (bool) $this->getProduct()->getAwGcAllowMessage();
    }

    /**
     * @return Varien_Object
     */
    public function getPreConfiguredValues()
    {
        $preConfiguredValues = $this->getProduct()->getPreconfiguredValues();
        if (null === $preConfiguredValues) {
            $preConfiguredValues = new Varien_Object;
        }
        return $preConfiguredValues;
    }

    public function isEEVersion()
    {
        return $this->helper('aw_giftcard')->isEEVersion();
    }

    public function getCurrentCurrency()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }
}