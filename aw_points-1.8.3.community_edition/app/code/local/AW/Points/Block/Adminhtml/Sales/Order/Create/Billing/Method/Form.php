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


class AW_Points_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{
    protected function _toHtml()
    {
        $this->setTemplate('aw_points/sales/order/create/billing/method/form.phtml');
        return parent::_toHtml();
    }

    public function getSummaryForCustomer()
    {
        if (!$this->_summaryForCustomer) {
            $this->_summaryForCustomer = Mage::getModel('points/summary')->loadByCustomer(
                $this->getQuote()->getCustomer()
            );
        }
        return $this->_summaryForCustomer;
    }

    public function getMoneyForPoints()
    {
        if (!$this->getData('money_for_points')) {
            try {
                $websiteId = Mage::app()->getStore($this->getQuote()->getStoreId())->getWebsiteId();
                $moneyForPoints = Mage::getModel('points/rate')
                    ->setCurrentCustomer($this->getQuote()->getCustomer())
                    ->setCurrentWebsite(Mage::app()->getWebsite($websiteId))
                    ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                    ->exchange($this->getSummaryForCustomer()->getPoints())
                ;
                $this->setData(
                    'money_for_points',
                    Mage::app()->getStore($this->getQuote()->getStoreId())->convertPrice($moneyForPoints, true)
                );
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

        $neededPoints = 0;
        try {
            $neededPoints = Mage::helper('points')->getNeededPoints(
                $sum, $this->getQuote()->getCustomer(), $this->getQuote()->getStoreId()
            );
        } catch (Exception $ex) {

        }
        return $neededPoints;
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
        return Mage::helper('points')->getLimitedPoints(
            $sum, $this->getQuote()->getCustomer(), $this->getQuote()->getStoreId()
        );
    }

    public function pointsSectionAvailable()
    {
        $isAvailable = $this->getSummaryForCustomer()->getPoints()
            && $this->getMoneyForPoints()
            && Mage::helper('points')->isAvailableToRedeem($this->getSummaryForCustomer()->getPoints())
            && $this->customerIsRegistered()
        ;
        if (!Mage::helper('points/config')->getCanUseWithCoupon()) {
            $isAvailable = $isAvailable && !$this->getQuote()->getData('coupon_code');
        }
        return $isAvailable;
    }

    public function urlToPointsSave()
    {
        return Mage::helper("adminhtml")->getUrl('points_admin/adminhtml_sales_order/savePoints');
    }

    protected function customerIsRegistered()
    {
        $customer = $this->getQuote()->getCustomer();
        return $customer->getId() > 0;
    }

    public function isPointsUsed()
    {
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getData('order_id');
        if (!$orderId) {
            return true;
        }
        $order = Mage::getModel('sales/order')->load($orderId);
        if (null === $order->getId()) {
            return true;
        }
        if ($order->getBaseMoneyForPoints() && $order->getMoneyForPoints()) {
            return true;
        }
        return false;
    }
}