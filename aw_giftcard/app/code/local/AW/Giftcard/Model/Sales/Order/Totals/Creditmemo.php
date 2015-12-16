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

class AW_Giftcard_Model_Sales_Order_Totals_Creditmemo extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditMemo)
    {
        $_result = parent::collect($creditMemo);
        $order = $creditMemo->getOrder();
        $store = Mage::app()->getStore($order->getStoreId());
        $website = $store->getWebsite();
        $baseTotal = $creditMemo->getBaseGrandTotal();

        $needBaseMoneyToRefund = abs($order->getBaseTotalPaid() - $order->getBaseTotalRefunded());
        if ($baseTotal > $needBaseMoneyToRefund
            && Mage::helper('aw_giftcard/config')->getAllowRefund($website)
                == AW_Giftcard_Model_Source_Giftcard_Config_Refund::ALLOW_AFTER_REAL_MONEY_VALUE
        ) {
            $this->_attachTotals(
                $creditMemo, AW_Giftcard_Model_Source_Giftcard_Config_Refund::ALLOW_AFTER_REAL_MONEY_VALUE
            );
        }

        if (Mage::helper('aw_giftcard/config')->getAllowRefund($website)
            == AW_Giftcard_Model_Source_Giftcard_Config_Refund::ALLOW_BEFORE_REAL_MONEY_VALUE
        ) {
            $this->_attachTotals(
                $creditMemo, AW_Giftcard_Model_Source_Giftcard_Config_Refund::ALLOW_BEFORE_REAL_MONEY_VALUE
            );
        }

        if (Mage::helper('aw_giftcard/config')->getAllowRefund($website)
            == AW_Giftcard_Model_Source_Giftcard_Config_Refund::NOT_ALLOW_VALUE
        ) {
            $this->_attachTotals(
                $creditMemo, AW_Giftcard_Model_Source_Giftcard_Config_Refund::NOT_ALLOW_VALUE
            );
        }
        return $_result;
    }


    protected function _attachTotals(Mage_Sales_Model_Order_Creditmemo $creditMemo, $mode)
    {
        $total = $creditMemo->getGrandTotal();
        $baseTotal = $creditMemo->getBaseGrandTotal();
        $order = $creditMemo->getOrder();
        $order->setForcedCanCreditmemo(false);
        if ($mode == AW_Giftcard_Model_Source_Giftcard_Config_Refund::ALLOW_AFTER_REAL_MONEY_VALUE
            || $mode == AW_Giftcard_Model_Source_Giftcard_Config_Refund::NOT_ALLOW_VALUE
        ) {
            $_needBaseMoneyToRefund = abs($order->getBaseTotalPaid() - $order->getBaseTotalRefunded());
            $_needMoneyToRefund = abs($order->getTotalPaid() - $order->getTotalRefunded());

            $total = $creditMemo->getGrandTotal() - $_needMoneyToRefund;
            $baseTotal = $creditMemo->getBaseGrandTotal() - $_needBaseMoneyToRefund;
        }

        if ($total <= 0 || $baseTotal <= 0) {
            return $this;
        }

        $baseTotalGiftcardAmount = 0;
        $totalGiftcardAmount = 0;
        $creditmemoGiftCards = array();
        if (null === $creditMemo->getId()) {
            $invoiceGiftCards = Mage::helper('aw_giftcard/totals')->getInvoicedGiftCardsByOrderId(
                $creditMemo->getOrder()->getId()
            );

            foreach ($invoiceGiftCards as $invoiceCard) {
                $_baseGiftcardAmount = $invoiceCard->getBaseGiftcardAmount();
                $_giftcardAmount = $invoiceCard->getGiftcardAmount();

                $creditmemoItems = Mage::helper('aw_giftcard/totals')->getAllCreditmemoForGiftCard(
                    $creditMemo->getOrder()->getId(), $invoiceCard->getGiftcardId()
                );

                if (count($creditmemoItems) > 0) {
                    foreach ($creditmemoItems as $creditmemoGiftcard) {
                        $_baseGiftcardAmount -= $creditmemoGiftcard->getBaseGiftcardAmount();
                        $_giftcardAmount -= $creditmemoGiftcard->getGiftcardAmount();
                    }
                }
                $baseCardUsedAmount = $_baseGiftcardAmount;
                if ($_baseGiftcardAmount >= $baseTotal) {
                    $baseCardUsedAmount = $baseTotal;
                }

                $baseTotal -= $baseCardUsedAmount;
                $cardUsedAmount = $_giftcardAmount;

                if ($_giftcardAmount >= $total) {
                    $cardUsedAmount = $total;
                }

                $total -= $cardUsedAmount;
                $_baseGiftcardAmount = round($baseCardUsedAmount, 4);
                $_giftcardAmount = round($cardUsedAmount, 4);

                $baseTotalGiftcardAmount += $_baseGiftcardAmount;
                $totalGiftcardAmount += $_giftcardAmount;

                $_creditmemoCard = new Varien_Object($invoiceCard->getData());
                $_creditmemoCard
                    ->setBaseGiftcardAmount($_baseGiftcardAmount)
                    ->setGiftcardAmount($_giftcardAmount)
                ;
                array_push($creditmemoGiftCards, $_creditmemoCard);
            }
        }

        if (null !== $creditMemo->getId() && $creditMemo->getAwGiftCards()) {
            $creditmemoGiftCards = $creditMemo->getAwGiftCards();
            foreach ($creditmemoGiftCards as $creditmemoCard) {
                $baseTotalGiftcardAmount += $creditmemoCard->getBaseGiftcardAmount();
                $totalGiftcardAmount += $creditmemoCard->getGiftcardAmount();
            }
        }

        if (count($creditmemoGiftCards) > 0) {
            $creditMemo->setAllowZeroGrandTotal(true);
        }

        $creditMemo
            ->setAwGiftCards($creditmemoGiftCards)
            ->setBaseAwGiftCardsAmount($baseTotalGiftcardAmount)
            ->setAwGiftCardsAmount($totalGiftcardAmount)
            ->setBaseGrandTotal($creditMemo->getBaseGrandTotal() - $baseTotalGiftcardAmount)
            ->setGrandTotal($creditMemo->getGrandTotal() - $totalGiftcardAmount)
        ;
        return $this;
    }
}