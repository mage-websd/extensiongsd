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


class AW_Points_Helper_Order extends Mage_Core_Helper_Abstract
{
    const MINIMUM_POSSIBLE_ERROR = 0.000000000001;

    public function calculatePartial($invoice, $accessModifier)
    {
        $order = $invoice->getOrder();

        if ($this->isSagePayMethod($order)) {
            $this->processOrderBeforePlace($order);
        }

        if ($order->getBaseMoneyForPoints() && $order->getMoneyForPoints()) {
            $moneyBaseToReduce = abs($order->getBaseMoneyForPoints());
            $moneyToReduce = abs($order->getMoneyForPoints());

            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }

                $orderItemQty = $orderItem->{"get$accessModifier"}();

                if ($orderItemQty) {
                    $itemToSubtotalMultiplier
                        = ($item->getData('base_row_total') + $item->getData('base_weee_tax_applied_row_amount'))
                        / $invoice->getOrder()->getBaseSubtotal()
                    ;

                    $moneyBaseToReduceItem = $moneyBaseToReduce * $itemToSubtotalMultiplier;
                    $moneyToReduceItem = $moneyToReduce * $itemToSubtotalMultiplier;

                    if (($item->getData('base_row_total') + $moneyBaseToReduceItem) < self::MINIMUM_POSSIBLE_ERROR) {
                        $invoice->setMoneyForPoints($invoice->getMoneyForPoints() + $item->getData('row_total'));
                        $invoice->setBaseMoneyForPoints(
                            $invoice->getBaseMoneyForPoints() + $item->getData('base_row_total')
                        );
                    } else {
                        $invoice->setGrandTotal($invoice->getGrandTotal() - $moneyToReduceItem);
                        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $moneyBaseToReduceItem);
                        $invoice->setMoneyForPoints($moneyToReduceItem + $invoice->getMoneyForPoints());
                        $invoice->setBaseMoneyForPoints($moneyBaseToReduceItem + $invoice->getBaseMoneyForPoints());
                    }
                }
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function isSagePayMethod($order)
    {
        if (stripos($order->getPayment()->getMethodInstance()->getCode(), 'sagepay') === false) {
            return false;
        }

        if (Mage::app()->getRequest()->getControllerModule() !== 'Ebizmarts_SagePaySuite') {
            return false;
        }

        return true;
    }

    public function processOrderBeforePlace($order)
    {
        $session = Mage::getSingleton('checkout/session');
        if (
            !$session->getData('use_points')
            || !$session->getData('points_amount')
            || ((int)$session->getData('points_amount') <= 0)
        ) {
            return $this;
        }

        if ($order->getCustomerIsGuest()) {
            return $this;
        }

        if ($order->getCustomerId()) {
            $quote = $order->getQuote();
            if (!$quote instanceof Mage_Sales_Model_Quote) {
                $quote = Mage::getModel('sales/quote')
                    ->setSharedStoreIds(array($order->getStoreId()))
                    ->load($order->getQuoteId())
                ;
            }
            $sum = floatval($quote->getData('base_subtotal_with_discount'));
            $pointsSpendCalculation = Mage::helper('points/config')->getPointsSpendingCalculation();
            if ($pointsSpendCalculation !== AW_Points_Helper_Config::BEFORE_TAX) {
                if ($quote->isVirtual()) {
                    $sum += $quote->getBillingAddress()->getData('base_tax_amount');
                } else {
                    $sum += $quote->getShippingAddress()->getData('base_tax_amount');
                }
            }
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            //compatibility with "Enable Automatic Assignment to Customer Group" option
            if (
                defined("Mage_Customer_Model_Observer::VIV_PROCESSED_FLAG")
                && Mage::registry(Mage_Customer_Model_Observer::VIV_PROCESSED_FLAG)
            ) {
                $customer->setData('group_id', $order->getCustomer()->getOrigData('group_id'));
            }
            $limitedPoints = Mage::helper('points')->getLimitedPoints($sum, $customer, $order->getStoreId());
            $pointsAmount = (int)$session->getData('points_amount');
            $customerPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

            if ($customerPoints < $pointsAmount
                || $limitedPoints < $pointsAmount
                || !Mage::helper('points')->isAvailableToRedeem($customerPoints)
            ) {
                Mage::throwException($this->__('Incorrect points amount'));
            }

            $amountToSubtract = -$pointsAmount;
            $moneyForPointsBase = Mage::getModel('points/api')->changePointsToMoney(
                $amountToSubtract, $customer, $order->getStore()->getWebsite()
            );
            $moneyForPoints = $order->getBaseCurrency()->convert($moneyForPointsBase, $order->getOrderCurrencyCode());
            $order->setAmountToSubtract($amountToSubtract);
            $order->setBaseMoneyForPoints($moneyForPointsBase);
            $order->setMoneyForPoints($moneyForPoints);
        }
    }
}