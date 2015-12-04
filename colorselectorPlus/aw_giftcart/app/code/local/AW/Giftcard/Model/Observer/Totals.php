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

class AW_Giftcard_Model_Observer_Totals extends Mage_Core_Model_Abstract
{
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        if ($observer->getBlock() instanceof Mage_Checkout_Block_Cart_Coupon) {
            $block = Mage::app()->getLayout()->createBlock('aw_giftcard/frontend_checkout_cart_giftcard');
            $observer->getTransport()->setHtml($observer->getTransport()->getHtml() . $block->toHtml());
        }
        return $this;
    }

    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $quote = $observer->getEvent()->getQuote();

        if ($quote && $quote->getBaseGrandTotal() == 0 && null !== $quote->getBaseAwGiftCardsAmount()) {
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            $result->isAvailable = ($paymentMethod === 'free')
                && empty($result->isDeniedInConfig);
        }
        return $this;
    }

    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quoteGiftcardsItems = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($order->getQuoteId());
        if (count($quoteGiftcardsItems) > 0) {
            foreach ($quoteGiftcardsItems as $card) {
                $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->load($card->getGiftcardId());
                $giftcardModel
                    ->setOrder($order)
                    ->setBalance($giftcardModel->getBalance() - $card->getBaseGiftcardAmount())
                    ->save()
                ;
            }
        }
        return $this;
    }

    public function salesOrderLoadAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quoteGiftcardsItems = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($order->getQuoteId());

        if (count($quoteGiftcardsItems) > 0) {
            $order->setAwGiftCards($quoteGiftcardsItems);
        }

        if (!$order->isCanceled()
            && $order->getState() != Mage_Sales_Model_Order::STATE_CLOSED
            && !$order->canCreditmemo()
            && count($quoteGiftcardsItems) > 0
        ) {
            foreach ($order->getAllItems() as $item) {
                if ($item->canRefund()) {
                    $order->setForcedCanCreditmemo(true);
                }
            }
        }
        return $this;
    }

    public function salesOrderSaveBefore(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        if ($order->hasForcedCanCreditmemo()) {
            $order->setForcedCanCreditmemo(false);
            foreach ($order->getAllItems() as $item) {
                if ($item->canRefund()) {
                    $order->setForcedCanCreditmemo(true);
                }
            }
        }
        return $this;
    }

    public function salesOrderCreditmemoRefund(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $creditmemo = $observer->getEvent()->getCreditmemo();
        $store = Mage::app()->getStore($creditmemo->getOrder()->getStoreId());
        $website = $store->getWebsite();
        if (!$creditmemo->getAwGiftCards() || count($creditmemo->getAwGiftCards()) == 0
            || Mage::helper('aw_giftcard/config')->getAllowRefund($website)
                == AW_Giftcard_Model_Source_Giftcard_Config_Refund::NOT_ALLOW_VALUE
        ) {
            return $this;
        }

        foreach ($creditmemo->getAwGiftCards() as $giftcard) {
            $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->load($giftcard->getGiftcardId());
            $giftcardModel
                ->setCreditmemo($creditmemo)
                ->setBalance($giftcardModel->getBalance() + $giftcard->getBaseGiftcardAmount())
            ;
            try {
                $giftcardModel->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    public function salesOrderInvoiceLoadAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $invoice = $observer->getEvent()->getInvoice();
        if (null !== $invoice->getId()) {
            $invoiceGiftcardsItems = Mage::helper('aw_giftcard/totals')->getInvoiceGiftCards($invoice->getId());
            $invoice->setAwGiftCards($invoiceGiftcardsItems);
        }
        return $this;
    }

    public function salesOrderCreditmemoLoadAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (null !== $creditmemo->getId()) {
            $creditmemoGiftcardsItems = Mage::helper('aw_giftcard/totals')
                ->getCreditmemoGiftCards($creditmemo->getId())
            ;
            $creditmemo->setAwGiftCards($creditmemoGiftcardsItems);
        }
        return $this;
    }

    public function salesOrderInvoiceSaveAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $invoice = $observer->getEvent()->getInvoice();
        if (!$invoice->getAwGiftCards() || count($invoice->getAwGiftCards()) == 0) {
            return $this;
        }

        foreach ($invoice->getAwGiftCards() as $giftcard) {
            $invoiceGiftcardModel = Mage::getModel('aw_giftcard/order_invoice_giftcard');
            $invoiceGiftcardModel
                ->setInvoiceEntityId($invoice->getId())
                ->setGiftcardId($giftcard->getGiftcardId())
                ->setBaseGiftcardAmount($giftcard->getBaseGiftcardAmount())
                ->setGiftcardAmount($giftcard->getGiftcardAmount())
            ;
            try {
                $invoiceGiftcardModel->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    public function salesOrderCreditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (!$creditmemo->getAwGiftCards() || count($creditmemo->getAwGiftCards()) == 0) {
            return $this;
        }

        foreach ($creditmemo->getAwGiftCards() as $giftcard) {
            $creditmemoGiftcardModel = Mage::getModel('aw_giftcard/order_creditmemo_giftcard');
            $creditmemoGiftcardModel
                ->setCreditmemoEntityId($creditmemo->getId())
                ->setGiftcardId($giftcard->getGiftcardId())
                ->setBaseGiftcardAmount($giftcard->getBaseGiftcardAmount())
                ->setGiftcardAmount($giftcard->getGiftcardAmount())
            ;
            try {
                $creditmemoGiftcardModel->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    public function paypalPrepareLineItems(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $paypalCart = $observer->getEvent()->getPaypalCart();
        $salesEntity = $observer->getEvent()->getSalesEntity();

        if (null === $salesEntity) {
            $salesEntity = $paypalCart->getSalesEntity();
        }

        if (null === $salesEntity) {
           return $this;
        }

        $giftcards = $salesEntity->getAwGiftCards();
        if ($salesEntity instanceof Mage_Sales_Model_Quote) {
            $giftcards = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($salesEntity->getId());
        }
        if ($salesEntity instanceof Mage_Sales_Model_Order) {
            $giftcards = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards($salesEntity->getQuoteId());
        }

        if (null === $giftcards || count($giftcards) == 0) {
            return $this;
        }

        $baseAmount = 0;
        foreach ($giftcards as $giftcard) {
            $baseAmount += $giftcard->getBaseGiftcardAmount();
        }

        $baseAmount = round($baseAmount, 4);
        if ($baseAmount > 0.0001 ) {
            if (is_null($paypalCart)) {
                $additionalItems = $observer->getEvent()->getAdditional();
                $itemList = $additionalItems->getItems();
                $itemList[] = new Varien_Object(
                    array(
                         'name'   => Mage::helper('aw_giftcard')->__('Gift Card(s)'),
                         'qty'    => 1,
                         'amount' => $baseAmount,
                    )
                );
                $additionalItems->setItems($itemList);
                $salesEntity->setBaseSubtotal($salesEntity->getBaseSubtotal() + $baseAmount);
                return $this;
            }
            $paypalCart->updateTotal(
                Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, $baseAmount,
                Mage::helper('aw_giftcard')->__(
                    'Gift Card (%s)', Mage::app()->getStore()->convertPrice($baseAmount, true, false)
                )
            );
        }
        return $this;
    }

    public function salesOrderCreateProcessData(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('aw_giftcard')->isModuleOutputEnabled()) {
            return $this;
        }

        $model = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote = $model->getQuote();
        if (array_key_exists('aw_giftcard_add', $request) && trim($request['aw_giftcard_add'])) {
            $giftcardCode = $request['aw_giftcard_add'];

            try {
                $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->loadByCode(trim($giftcardCode));
                if (null === $giftcardModel->getId()) {
                    throw new Exception(
                        Mage::helper('aw_giftcard')->__(
                            'Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftcardCode)
                        )
                    );
                }
                Mage::helper('aw_giftcard/totals')->addCardToQuote($giftcardModel, $quote);
                Mage::getSingleton('adminhtml/session_quote')->addSuccess(
                    Mage::helper('aw_giftcard')->__('Gift Card "%s" has been added.',
                        Mage::helper('core')->escapeHtml($giftcardModel->getCode())
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    Mage::helper('aw_giftcard')->__($e->getMessage())
                );
            }
        }

        if (array_key_exists('aw_giftcard_remove', $request) && trim($request['aw_giftcard_remove'])) {
            $giftcardCode = $request['aw_giftcard_remove'];

            try {
                Mage::helper('aw_giftcard/totals')->removeCardFromQuote(trim($giftcardCode), $quote);
                Mage::getSingleton('adminhtml/session_quote')->addSuccess(
                    Mage::helper('aw_giftcard')->__('Gift Card "%s" has been removed.',
                        Mage::helper('core')->escapeHtml($giftcardCode)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    Mage::helper('aw_giftcard')->__($e->getMessage())
                );
            }
        }
        return $this;
    }

    public function orderPaymentCancel(Varien_Event_Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();
        $store = Mage::app()->getStore($order->getStoreId());
        $website = $store->getWebsite();
        if (count($order->getAwGiftCards()) != 0 && Mage::helper('aw_giftcard/config')->getAllowRefund($website)
            !== AW_Giftcard_Model_Source_Giftcard_Config_Refund::NOT_ALLOW_VALUE
        ) {
            foreach ($order->getAwGiftCards() as $giftcard) {
                $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->load($giftcard->getGiftcardId());
                $giftcardModel
                    ->setCreditmemo($order)
                    ->setBalance($giftcardModel->getBalance() + $giftcard->getBaseGiftcardAmount())
                ;
                try {
                    $giftcardModel->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
    }
}