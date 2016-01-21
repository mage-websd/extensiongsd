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


class AW_Points_Model_Total_Quote_Points extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('points');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        $session = Mage::getSingleton('checkout/session');
        if (is_null($session->getQuoteId())) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        }
        $isCustomerLoggedIn = (bool)$quote->getCustomer()->getId();

        if ($session->getData('use_points') && $address->getBaseGrandTotal() && $isCustomerLoggedIn) {
            $customer = Mage::getModel('customer/customer')->load($quote->getCustomer()->getId());
            $pointsAmountUsed = abs($session->getData('points_amount'));
            $pointsAmountAllowed = Mage::getModel('points/summary')
                ->loadByCustomer($customer)
                ->getPoints()
            ;

            $storeId = $quote->getStoreId();
            $website = Mage::app()->getWebsite(Mage::app()->getStore($storeId)->getWebsiteId());

            $baseSubtotal = $address->getData('base_subtotal');
            $subtotal = $address->getData('subtotal');
            $baseSubtotalWithDiscount = $address->getData('base_subtotal') + $address->getData('base_discount_amount');
            $subtotalWithDiscount = $address->getData('subtotal') + $address->getData('discount_amount');

            $adjustTaxValues = false;
            $pointsSpendingCalculation = Mage::helper('points/config')->getPointsSpendingCalculation();
            if ($pointsSpendingCalculation !== AW_Points_Helper_Config::BEFORE_TAX) {
                $baseSubtotalWithDiscount += $address->getData('base_tax_amount');
                $subtotalWithDiscount += $address->getData('tax_amount');
            } else {
                $adjustTaxValues = true;
            }

            $limitedPoints = Mage::helper('points')->getLimitedPoints($baseSubtotalWithDiscount, $customer, $storeId);
            $pointsAmountUsed = min($pointsAmountUsed, $pointsAmountAllowed, $limitedPoints);

            $session->setData('points_amount', $pointsAmountUsed);
            $rate = Mage::getModel('points/rate')
                ->setCurrentCustomer($customer)
                ->setCurrentWebsite($website)
                ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
            ;

            if (!$rate->getId()) {
                return $this;
            }

            $moneyBaseCurrencyForPoints = $rate->exchange($pointsAmountUsed);
            $moneyCurrentCurrencyForPoints = Mage::app()->getStore()->convertPrice($moneyBaseCurrencyForPoints);
            /**
             * If points amount is more then needed to pay for subtotal with discount for order,
             * we need to set new points amount
             */
            if ($moneyBaseCurrencyForPoints > $baseSubtotalWithDiscount) {
                $neededAmount = ceil($baseSubtotalWithDiscount * $rate->getPoints() / $rate->getMoney());
                $neededAmountBaseCurrency = $rate->exchange($neededAmount);
                $neededAmountCurrentCurrency = Mage::app()->getStore()->convertPrice($neededAmountBaseCurrency);
                $session->setData('points_amount', $neededAmount);
                $address->setGrandTotal($address->getData('grand_total') - $subtotalWithDiscount);
                $address->setBaseGrandTotal($address->getData('base_grand_total') - $baseSubtotalWithDiscount);
                $address->setMoneyForPoints($neededAmountCurrentCurrency);
                $address->setBaseMoneyForPoints($neededAmountBaseCurrency);
                $quote->setMoneyForPoints($neededAmountCurrentCurrency);
                $quote->setBaseMoneyForPoints($neededAmountBaseCurrency);
            } else {
                $adjustGrandTotalValue = 0;
                $adjustBaseGrandTotalValue = 0;
                if ($adjustTaxValues) {
                    $taxableSubtotal = $subtotal;
                    $taxableBaseSubtotal = $baseSubtotal;
                    $taxableSubtotalWithDiscount = $subtotalWithDiscount;
                    $taxableBaseSubtotalWithDiscount = $baseSubtotalWithDiscount;
                    foreach ($address->getAllItems() as $item) {
                        if (!$item->getTaxAmount()) {
                            $taxableSubtotal -= $item->getRowTotal();
                            $taxableBaseSubtotal -= $item->getBaseRowTotal();
                            $taxableSubtotalWithDiscount -= $item->getRowTotal();
                            $taxableBaseSubtotalWithDiscount -= $item->getBaseRowTotal();
                        }
                    }

                    if (Mage::getModel('tax/config')->applyTaxAfterDiscount()) {
                        $taxCalculationSubtotal = $taxableSubtotalWithDiscount;
                        $taxCalculationBaseSubtotal = $taxableBaseSubtotalWithDiscount;
                    } else {
                        $taxCalculationSubtotal = $taxableSubtotal;
                        $taxCalculationBaseSubtotal = $taxableBaseSubtotal;
                    }

                    $origTaxAmount = $address->getData('tax_amount');
                    $origBaseTaxAmount = $address->getData('base_tax_amount');
                    $taxPercent = $taxCalculationSubtotal > 0 ? ($origTaxAmount * 100) / $taxCalculationSubtotal : 0;
                    $baseTaxPercent = $taxCalculationBaseSubtotal > 0 ? ($origBaseTaxAmount * 100) / $taxCalculationBaseSubtotal : 0;

                    $newTaxAmount = max($taxCalculationSubtotal - $moneyCurrentCurrencyForPoints, 0) * $taxPercent / 100;
                    $newBaseTaxAmount = max($taxCalculationBaseSubtotal - $moneyBaseCurrencyForPoints, 0) * $baseTaxPercent / 100;

                    $adjustGrandTotalValue = Mage::getSingleton('tax/calculation')->round($origTaxAmount - $newTaxAmount);
                    $adjustBaseGrandTotalValue = Mage::getSingleton('tax/calculation')->round($origBaseTaxAmount - $newBaseTaxAmount);

                    $address->setTaxAmount($newTaxAmount);
                    $address->setBaseTaxAmount($newBaseTaxAmount);
                }
                $address->setGrandTotal($address->getGrandTotal() - $moneyCurrentCurrencyForPoints - $adjustGrandTotalValue);
                $address->setBaseGrandTotal($address->getBaseGrandTotal() - $moneyBaseCurrencyForPoints - $adjustBaseGrandTotalValue);

                $address->setMoneyForPoints($moneyCurrentCurrencyForPoints);
                $address->setBaseMoneyForPoints($moneyBaseCurrencyForPoints);
                $quote->setMoneyForPoints($moneyCurrentCurrencyForPoints);
                $quote->setBaseMoneyForPoints($moneyBaseCurrencyForPoints);
            }
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $session = Mage::getSingleton('checkout/session');
        if (is_null($session->getQuote()->getId())) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        }
        $quote = $address->getQuote();
        if ($address->getMoneyForPoints()) {
            $description = $session->getData('points_amount');
            $moneyForPoints = $address->getMoneyForPoints();
            $textForPoints = Mage::helper('points/config')->getPointUnitName($quote->getStoreId());
            if ($description) {
                $title = Mage::helper('sales')->__('%s (%s)', $textForPoints, $description);
            } else {
                $title = Mage::helper('sales')->__('%s', $textForPoints);
            }
            $address->addTotal(
                array(
                     'code'  => $this->getCode(),
                     'title' => $title,
                     'value' => -$moneyForPoints,
                )
            );
        }
        return $this;
    }
}
