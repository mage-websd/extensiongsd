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

class AW_Giftcard_Model_Sales_Order_Totals_Quote extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('aw_giftcard');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $_result = parent::collect($address);

        $baseTotal = $address->getBaseGrandTotal();
        $total = $address->getGrandTotal();

        $baseTotalGiftcardAmount = 0;
        $totalGiftcardAmount = 0;

        $quote = $address->getQuote();

        if (
            $baseTotal
            && (
                $quote->getIsActive()
            || Mage::app()->getStore()->isAdmin() // when quote is created on the backend it gets is_active = false immediately, which prevents us from calculating the GC discount
            )
        ) {
            $quoteCards = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($quote->getId());
            foreach ($quoteCards as $quoteCard) {
                $_baseGiftcardAmount = $quoteCard->getCardBalance();

                $_giftcardAmount = $quote->getStore()->roundPrice(
                    $quote->getStore()->convertPrice($quoteCard->getCardBalance())
                );

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

                Mage::helper('aw_giftcard/totals')
                    ->saveQuoteGiftcardTotals($quoteCard->getLinkId(), $_baseGiftcardAmount, $_giftcardAmount)
                ;
            }
            $address
                ->getQuote()
                ->setBaseAwGiftCardsAmount($baseTotalGiftcardAmount)
                ->setAwGiftCardsAmount($totalGiftcardAmount)
            ;

            $address
                ->setBaseAwGiftCardsAmount($baseTotalGiftcardAmount)
                ->setAwGiftCardsAmount($totalGiftcardAmount)
                ->setBaseGrandTotal($address->getBaseGrandTotal() - $baseTotalGiftcardAmount)
                ->setGrandTotal($address->getGrandTotal() - $totalGiftcardAmount)
            ;
        }
        return $_result;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $_result = parent::fetch($address);

        $giftCards = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($address->getQuote()->getId());
        $address->addTotal(
            array(
                'code'       => $this->getCode(),
                'title'      => Mage::helper('aw_giftcard')->__('Gift Cards'),
                'value'      => -$address->getAwGiftCardsAmount(),
                'gift_cards' => $giftCards,
            )
        );
        return $_result;
    }
}
