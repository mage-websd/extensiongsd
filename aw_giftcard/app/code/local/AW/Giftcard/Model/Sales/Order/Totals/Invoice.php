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

class AW_Giftcard_Model_Sales_Order_Totals_Invoice extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $_result = parent::collect($invoice);

        $baseTotal = $invoice->getBaseGrandTotal();
        $total = $invoice->getGrandTotal();

        $baseTotalGiftcardAmount = 0;
        $totalGiftcardAmount = 0;
        $invoiceGiftCards = array();
        if (null === $invoice->getId()) {
            $quoteGiftcardsItems = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards(
                $invoice->getOrder()->getQuoteId()
            );

            foreach ($quoteGiftcardsItems as $quoteCard) {
                $_baseGiftcardAmount = $quoteCard->getBaseGiftcardAmount();
                $_giftcardAmount = $quoteCard->getGiftcardAmount();

                $invoices = Mage::helper('aw_giftcard/totals')->getAllInvoicesForGiftCard(
                    $invoice->getOrder()->getId(), $quoteCard->getGiftcardId()
                );
                if (count($invoices) > 0) {
                    foreach ($invoices as $giftcardInvoice) {
                        $_baseGiftcardAmount -= $giftcardInvoice->getBaseGiftcardAmount();
                        $_giftcardAmount -= $giftcardInvoice->getGiftcardAmount();
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

                $_invoiceCard = new Varien_Object($quoteCard->getData());
                $_invoiceCard
                    ->setBaseGiftcardAmount($_baseGiftcardAmount)
                    ->setGiftcardAmount($_giftcardAmount)
                ;
                array_push($invoiceGiftCards, $_invoiceCard);
            }
        }

        if (null !== $invoice->getId() && $invoice->getAwGiftCards()) {
            $invoiceGiftCards = $invoice->getAwGiftCards();
            foreach ($invoiceGiftCards as $invoiceCard) {
                $baseTotalGiftcardAmount += $invoiceCard->getBaseGiftcardAmount();
                $totalGiftcardAmount += $invoiceCard->getGiftcardAmount();
            }
        }

        $invoice
            ->setAwGiftCards($invoiceGiftCards)
            ->setBaseAwGiftCardsAmount($baseTotalGiftcardAmount)
            ->setAwGiftCardsAmount($totalGiftcardAmount)
            ->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalGiftcardAmount)
            ->setGrandTotal($invoice->getGrandTotal() - $totalGiftcardAmount)
        ;
        return $_result;
    }
}