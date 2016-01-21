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


class AW_Points_Block_Catalog_Product_Points extends Mage_Core_Block_Template
{
    public function canShow()
    {
        return Mage::helper('points/config')->getShowPointsBlockAtProductPage() && ($this->getPoints() > 0);
    }

    public function getPoints()
    {
        if (is_null($this->getData('points'))) {
            try {
                $pointsSummary = 0;
                $product = Mage::registry('current_product');
                $price = $product->getFinalPrice();
                $storeId = $product->getStore()->getId();
                if (Mage::helper('points/config')->getPointsCollectionOrder($storeId) == AW_Points_Helper_Config::AFTER_TAX) {
                    $price = Mage::helper('tax')->getPrice($product, $price, true);
                }
                /* Points amount after order complete */
                $pointsSummary += Mage::getModel('points/rate')
                    ->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS)
                    ->exchange($price)
                ;

                $maximumPointsPerCustomer = Mage::helper('points/config')->getMaximumPointsPerCustomer();
                if ($maximumPointsPerCustomer) {
                    $customersPoints = 0;
                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    if ($customer) {
                        $customersPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();
                    }
                    if ($pointsSummary + $customersPoints > $maximumPointsPerCustomer) {
                        $pointsSummary = $maximumPointsPerCustomer - $customersPoints;
                    }
                }
                $this->setData('points', $pointsSummary);
            } catch (Exception $e) {

            }
        }
        return $this->getData('points');
    }

    public function getMoney()
    {
        if (is_null($this->getData('money'))) {
            $money = 0;
            try {
                $money = Mage::getModel('points/rate')
                    ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                    ->exchange($this->getPoints())
                ;
            } catch (Exception $e) {

            }
            $this->setData('money', $money);
        }
        return $this->getData('money');
    }

    public function customerIsGuest()
    {
        return Mage::getModel('customer/session')->getCustomer()->getId() ? false : true;
    }
}