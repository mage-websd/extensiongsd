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


class AW_Points_Block_Customer_Reward_Summary extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setData('customer', Mage::getSingleton('customer/session')->getCustomer());
        $this->setData('website', Mage::app()->getWebsite());

        $this->addData(
            Mage::getModel('points/summary')->loadByCustomer($this->getCustomer())->getData()
        );
    }

    public function getInfoPageUrl()
    {
        $toReturn = "";

        if (Mage::helper('points/config')->getInfoPageId()) {
            $toReturn = Mage::helper('points')->getBaseUrl("points/index/infopage/");
        }

        return $toReturn;
    }

    public function getCurrentPointsBalance()
    {
        return (int)$this->getPoints();
    }

    public function getPointsConvertedIntoMoney()
    {
        $result = 0;
        try {
            $result = Mage::getModel('points/api')->changePointsToMoney(
                $this->getPoints(), $this->getCustomer(), $this->getWebsite()
            );
            if ($result > 0) {
                $result = Mage::app()->getStore()->convertPrice($result, true);
            }
        } catch (Exception $e) {
            Mage::helper('awcore/logger')->log(
                $this,
                Mage::helper('points')->__(
                    'Unable to convert points into currency to show up in summary section of user account page.'
                ),
                AW_Core_Model_Logger::LOG_SEVERITY_WARNING,
                $e->getMessage(),
                $e->getLine()
            );
        }
        return $result;
    }

    public function getPointsFromRatePointsToCurrency()
    {
        $this->_loadExchangeRateByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
        return $this->getRate()->getPoints();
    }

    public function getCurrencyFromRatePointsToCurrency()
    {
        $this->_loadExchangeRateByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
        return Mage::app()->getStore()->convertPrice($this->getRate()->getMoney(), true);
    }

    public function getCurrencyFromRateCurrencyToPoints()
    {
        $this->_loadExchangeRateByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS);
        return Mage::app()->getStore()->convertPrice($this->getRate()->getMoney(), true);
    }

    public function getPointsFromRateCurrencyToPoints()
    {
        $this->_loadExchangeRateByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS);
        return $this->getRate()->getPoints();
    }

    public function getBalanceLimitationHigh()
    {
        return Mage::helper('points/config')->getMaximumPointsPerCustomer();
    }

    public function getBalanceLimitationLow()
    {
        $lowLimit = 0;
        $limit = Mage::helper('points/config')->getMinimumPointsToRedeem();
        if ($limit > $this->getPoints()) {
            $lowLimit = $limit;
        }
        return $lowLimit;
    }

    public function getPointUnitName()
    {
        return Mage::helper('points/config')->getPointUnitName();
    }

    public function getExpiration()
    {
        $expirationToReturn = 0;
        if ($this->getPoints() > 0) {
            $expirationToReturn = Mage::helper('points/config')->getPointsExpirationDays();
        }
        return $expirationToReturn;
    }

    protected function _loadExchangeRateByDirection($direction)
    {
        Mage::getModel('points/rate')->setCurrentCustomer($this->getCustomer());
        Mage::getModel('points/rate')->setCurrentWebsite($this->getWebsite());
        $rate = Mage::getModel('points/rate')->loadByDirection($direction);
        $this->setData('rate', $rate);
        return $this;
    }

    protected function _toHtml()
    {
        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;
        if (Mage::helper('points')->magentoLess14()) {
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;
        }
        $this->setTemplate("aw_points/customer/" . $magentoVersionTag . "/reward/summary.phtml");
        $html = parent::_toHtml();
        return $html;
    }
}