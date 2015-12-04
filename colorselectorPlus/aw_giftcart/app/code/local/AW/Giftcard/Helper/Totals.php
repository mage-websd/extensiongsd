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

class AW_Giftcard_Helper_Totals extends Mage_Core_Helper_Abstract
{
    public function getQuoteGiftCards($quoteId = null)
    {
        if (null === $quoteId) {
            $quoteId = $this->getQuote()->getId();
        }
        $quoteGiftcardsCollection = Mage::getModel('aw_giftcard/quote_giftcard')->getCollection();
        $quoteGiftcardsCollection
            ->joinGiftCardTable()
            ->setFilterByQuoteId($quoteId)
        ;
        return $quoteGiftcardsCollection->getItems();
    }

    public function getInvoiceGiftCards($invoiceId)
    {
        $invoiceGiftcardsCollection = Mage::getModel('aw_giftcard/order_invoice_giftcard')->getCollection();
        $invoiceGiftcardsCollection
            ->joinGiftCardTable()
            ->setFilterByInvoiceId($invoiceId)
        ;
        return $invoiceGiftcardsCollection->getItems();
    }

    public function getCreditmemoGiftCards($creditmemoId)
    {
        $creditmemoGiftcardsCollection = Mage::getModel('aw_giftcard/order_creditmemo_giftcard')->getCollection();
        $creditmemoGiftcardsCollection
            ->joinGiftCardTable()
            ->setFilterByCreditmemoId($creditmemoId)
        ;
        return $creditmemoGiftcardsCollection->getItems();
    }

    public function getAllCreditmemoForGiftCard($orderId, $giftcardId)
    {
        $creditmemoIds = Mage::getResourceModel('sales/order_creditmemo_collection')
            ->setOrderFilter($orderId)
            ->getAllIds()
        ;

        $creditmemoGiftcardsCollection = Mage::getModel('aw_giftcard/order_creditmemo_giftcard')->getCollection();
        $creditmemoGiftcardsCollection
            ->joinGiftCardTable()
            ->setFilterByCreditmemoIds($creditmemoIds)
            ->setFilterByCardId($giftcardId)
        ;
        return $creditmemoGiftcardsCollection->getItems();
    }

    public function getAllInvoicesForGiftCard($orderId, $giftcardId)
    {
        $invoiceIds = Mage::getResourceModel('sales/order_invoice_collection')
            ->setOrderFilter($orderId)
            ->getAllIds()
        ;

        $invoiceGiftcardsCollection = Mage::getModel('aw_giftcard/order_invoice_giftcard')->getCollection();
        $invoiceGiftcardsCollection
            ->joinGiftCardTable()
            ->setFilterByInvoiceIds($invoiceIds)
            ->setFilterByCardId($giftcardId)
        ;
        return $invoiceGiftcardsCollection->getItems();
    }

    public function getInvoicedGiftCardsByOrderId($orderId)
    {
        $invoiceIds = Mage::getResourceModel('sales/order_invoice_collection')
            ->setOrderFilter($orderId)
            ->getAllIds()
        ;

        $invoiceGiftcardsCollection = Mage::getModel('aw_giftcard/order_invoice_giftcard')->getCollection();
        $invoiceGiftcardsCollection
            ->joinGiftCardTable()
            ->addSumBaseAmountToFilter()
            ->addSumAmountToFilter()
            ->groupBy('giftcard_id')
            ->setFilterByInvoiceIds($invoiceIds)
        ;
        return $invoiceGiftcardsCollection->getItems();
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function saveQuoteGiftcardTotals($linkId, $baseAmount, $amount)
    {
        $quoteGiftcardModel = Mage::getModel('aw_giftcard/quote_giftcard')->load($linkId);
        if (null !== $quoteGiftcardModel) {
            $quoteGiftcardModel
                ->setBaseGiftcardAmount($baseAmount)
                ->setGiftcardAmount($amount)
                ->save()
            ;
        }
        return $this;
    }

    public function addCardToQuote(AW_Giftcard_Model_Giftcard $giftcard, $quote = null)
    {
        if (null === $quote) {
            $quote = $this->getQuote();
        }

        if ($giftcard->isValidForRedeem($quote->getStoreId())) {
            $_collection = Mage::getModel('aw_giftcard/quote_giftcard')->getCollection();
            $_collection
                ->setFilterByQuoteId($quote->getId())
                ->setFilterByGiftcardId($giftcard->getId())
            ;

            if ($_collection->getSize() > 0) {
                throw new Exception('This gift card is already in the quote');
            }

            Mage::getModel('aw_giftcard/quote_giftcard')
                ->setQuoteEntityId($quote->getId())
                ->setGiftcardId($giftcard->getId())
                ->save()
            ;
        }
        return $this;
    }

    public function removeCardFromQuote($giftcardCode, $quote = null)
    {
        if (null === $quote) {
            $quote = $this->getQuote();
        }
        $quoteGiftcardsItems = $this->getQuoteGiftCards($quote->getId());
        $cardFound = false;
        foreach ($quoteGiftcardsItems as $card) {
            if ($card->getCode() == $giftcardCode) {
                $cardFound = true;
                $card->delete();
                break;
            }
        }
        if (false === $cardFound) {
            throw new Exception('Cannot remove gift card.');
        }
        return $this;
    }
}