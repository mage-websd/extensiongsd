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
 * @package    AW_Points
 * @version    1.8.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Checkout_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    protected $_summaryForCustomer;

    protected function _toHtml()
    {
        $quote = $this->getQuote();
        if (
            $quote->getBaseGrandTotal() == 0 &&
            $quote->hasBaseAwGiftCardsAmount() &&
            $this->_getBaseAwGiftCardAmount() != 0 &&
            ($quote->hasBaseMoneyForPoints() ? $quote->getBaseMoneyForPoints() : 0) === 0
        ) {
            return parent::_toHtml();
        }
        foreach ($quote->getAllItems() as $item) {
            if ($item->getIsRecurring()) {
                return parent::_toHtml();
            }
        }

        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;
        if (Mage::helper('points')->magentoLess14()) {
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;
        }
        if (!in_array('checkout_onepage_index', $this->getLayout()->getUpdate()->getHandles())) {
            $this->setTemplate('aw_points/checkout/onepage/payment/' . $magentoVersionTag . '/methods.phtml');
        } else {
            $this->setTemplate('aw_points/checkout/onepage/payment/info.phtml');
        }
        return parent::_toHtml();
    }

    public function getSummaryForCustomer()
    {
        if (!$this->_summaryForCustomer) {
            $this->_summaryForCustomer = Mage::getModel('points/summary')->loadByCustomer(
                Mage::getSingleton('customer/session')->getCustomer()
            );
        }
        return $this->_summaryForCustomer;
    }

    public function getMoneyForPoints()
    {
        if (!$this->getData('money_for_points')) {
            try {
                $moneyForPoints = Mage::getModel('points/rate')
                    ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                    ->exchange($this->getSummaryForCustomer()->getPoints());
                $this->setData('money_for_points', Mage::app()->getStore()->convertPrice($moneyForPoints, true));
            } catch (Exception $ex) {

            }
        }
        return $this->getData('money_for_points');
    }

    public function getNeededPoints()
    {
        $sum = $this->getQuote()->getData('base_subtotal_with_discount');
        if (Mage::helper('points/config')->getPointsSpendingCalculation() !== AW_Points_Helper_Config::BEFORE_TAX) {
            if ($this->getQuote()->isVirtual()) {
                $sum += $this->getQuote()->getBillingAddress()->getData('base_tax_amount');
            } else {
                $sum += $this->getQuote()->getShippingAddress()->getData('base_tax_amount');
            }
        }
        $sum -= $this->_getBaseAwGiftCardAmount();
        return Mage::helper('points')->getNeededPoints($sum);
    }

    public function getLimitedPoints()
    {
        $sum = $this->getQuote()->getData('base_subtotal_with_discount');
        if (Mage::helper('points/config')->getPointsSpendingCalculation() !== AW_Points_Helper_Config::BEFORE_TAX) {
            if ($this->getQuote()->isVirtual()) {
                $sum += $this->getQuote()->getBillingAddress()->getData('base_tax_amount');
            } else {
                $sum += $this->getQuote()->getShippingAddress()->getData('base_tax_amount');
            }
        }
        $sum -= $this->_getBaseAwGiftCardAmount();
        $sum -= Mage::getSingleton('customer/session')->getRafDiscountCustomer();
        $sum -= Mage::getSingleton('customer/session')->getRafMoneyCustomer();
        return Mage::helper('points')->getLimitedPoints($sum);
    }

    public function getBaseGrandTotalInPoints()
    {
        return Mage::helper('points')->getNeededPoints($this->getQuote()->getBaseGrandTotal());
    }

    public function getBaseGrandTotalInPointsToPay()
    {
        $session = Mage::getSingleton('checkout/session');
        $baseGrandTotalInPoints = Mage::helper('points')->getNeededPoints($this->getQuote()->getBaseGrandTotal());
        if ($session->getData('use_points')
            && $session->getData('points_amount')
            && (int)$session->getData('points_amount') > 0) {
            return $baseGrandTotalInPoints - ($this->getNeededPoints() - (int)$session->getData('points_amount'));
        } else {
            return $baseGrandTotalInPoints - $this->getNeededPoints();
        }
    }

    public function getSpendRatePoints()
    {
        $rate = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
        if (!$rate->getMoney() || !$rate->getPoints()) {
            return 0;
        }
        return $rate->getMoney()/$rate->getPoints();
    }

    public function pointsSectionAvailable()
    {
        $isAvailable = $this->getSummaryForCustomer()->getPoints()
            && $this->getMoneyForPoints()
            && Mage::helper('points')->isAvailableToRedeem($this->getSummaryForCustomer()->getPoints())
            && $this->customerIsRegistered()
            && $this->getNeededPoints()
        ;
        if (!Mage::helper('points/config')->getCanUseWithCoupon()) {
            $isAvailable = $isAvailable && !$this->getQuote()->getData('coupon_code');
        }
        return $isAvailable;
    }

    public function getFreePaymentMethod()
    {
        return Mage::getModel('payment/method_free');
    }

    protected function customerIsRegistered()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer->getId() > 0;
    }

    protected function _getBaseAwGiftCardAmount()
    {
        $quote = $this->getQuote();
        return $quote->hasBaseAwGiftCardsAmount() ? $quote->getBaseAwGiftCardsAmount() : 0;
    }
}