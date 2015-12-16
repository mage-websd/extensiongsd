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


class AW_Giftcard_Model_Observer_Giftcard extends Mage_Core_Model_Abstract
{
    public function productEditPrepareForm(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        if ($elem = $form->getElement('aw_gc_amounts')) {
            $elem->setRenderer(
                Mage::app()->getLayout()->createBlock('aw_giftcard/adminhtml_catalog_product_render_amount')
            );
        }
        return $this;
    }

    public function productExcludedFieldList(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getObject();
        $list = $block->getFormExcludedFieldList();
        $list[] = 'aw_gc_amounts';
        $block->setFormExcludedFieldList($list);
        return $this;
    }

    public function convertQuoteItemToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $_product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() != AW_Giftcard_Model_Catalog_Product_Type_Giftcard::TYPE_CODE) {
            return $this;
        }

        $giftCardOptions = array(
            'aw_gc_amounts',
            'aw_gc_custom_amount',
            'aw_gc_sender_name',
            'aw_gc_sender_email',
            'aw_gc_recipient_name',
            'aw_gc_recipient_email',
            'aw_gc_message',
        );

        $productOptions = $orderItem->getProductOptions();
        foreach ($giftCardOptions as $_optionKey) {
            if ($option = $quoteItem->getProduct()->getCustomOption($_optionKey)) {
                $productOptions[$_optionKey] = $option->getValue();
            }
        }

        $store = Mage::app()->getStore($orderItem->getStoreId());
        $website = $store->getWebsite();

        $expireAfter = Mage::helper('aw_giftcard/config')->getExpireValue($website);
        if (!$_product->getAwGcConfigExpire()) {
            $expireAfter = $_product->getAwGcExpire();
        }
        $productOptions['aw_gc_expire_at'] = $expireAfter;

        $emailTemplate = Mage::helper('aw_giftcard/config')->getEmailTemplate($store);
        if (!$_product->getAwGcConfigEmailTemplate()) {
            $emailTemplate = $_product->getAwGcEmailTemplate();
        }
        $productOptions['aw_gc_email_template'] = $emailTemplate;

        $giftCardTypeValue = $_product->getTypeInstance()->getGiftcardTypeValue($_product);
        $productOptions['aw_gc_type'] = $giftCardTypeValue;
        $productOptions['aw_gc_created_codes'] = array();

        $orderItem->setProductOptions($productOptions);

        return $this;
    }

    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $store = Mage::app()->getStore($order->getStoreId());
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() != AW_Giftcard_Model_Catalog_Product_Type_Giftcard::TYPE_CODE) {
                continue;
            }

            $qty = 0;
            $options = $item->getProductOptions();
            if (Mage::helper('aw_giftcard/config')->getAllowOrderItemStatus($store->getWebsite())
                == Mage_Sales_Model_Order_Item::STATUS_PENDING
            ) {
                $qty += $item->getQtyOrdered();
            }

            if (Mage::helper('aw_giftcard/config')->getAllowOrderItemStatus($store->getWebsite())
                == Mage_Sales_Model_Order_Item::STATUS_INVOICED) {
                $qty += $item->getQtyInvoiced();
            }

            if (count($options['aw_gc_created_codes']) > 0) {
                $qty -= count($options['aw_gc_created_codes']);
            }

            $amount = $item->getBasePrice();
            $websiteId = $store->getWebsite()->getWebsiteId();
            $balance = Mage::app()->getLocale()
                ->currency($store->getBaseCurrencyCode())
                ->toCurrency($amount, array('display' => Zend_Currency::NO_SYMBOL))
            ;
            $balance = Mage::app()->getLocale()->getNumber($balance);
            $createdCodesArray = array();
            $codeErrorFlag = false;
            $_emailSent = AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::DISABLED_LABEL;
            if ($qty > 0) {
                for ($i = 0; $i < $qty; $i++) {
                    try {
                        $giftCardModel = $this->_createGiftcard($item, $order, $balance, $websiteId);
                        array_push($createdCodesArray, $giftCardModel->getCode());
                    } catch (Mage_Core_Exception $e) {
                        Mage::logException($e);
                        $codeErrorFlag = true;
                    }
                }
                $_optionGiftCodes = array_merge($createdCodesArray, $options['aw_gc_created_codes']);
                $options['aw_gc_created_codes'] = $_optionGiftCodes;
            }

            $emailErrorFlag = false;
            if (count($createdCodesArray) > 0 && $item->getProductOptionByCode('aw_gc_recipient_email')) {
                $emailTemplate = Mage::getModel('aw_giftcard/email_template');

                try {
                    $_templateData = $options;
                    $balanceWithCurrency = Mage::app()->getLocale()
                        ->currency($store->getBaseCurrencyCode())
                        ->toCurrency($balance)
                    ;
                    $_templateData['balance'] = $balanceWithCurrency;
                    /*$emailTemplate->prepareEmailAndSend($_templateData, $store);
                    if ($emailTemplate->getSentSuccess()) {
                        $_emailSent = AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::ENABLED_LABEL;
                    }*/
                    $_schedule = $_templateData['info_buyRequest']['gift_mail_delivery_date'];
                    $_schedule = strtotime($_schedule);
                    $_emailQueue = Mage::getModel('aw_giftcard/emailqueue');
                    $_emailQueue->setData(array(
                        'template_data' => serialize($_templateData),
                        'store' => serialize($store),
                        'item' => $item->getProductId(),
                        'schedule' => $_schedule,
                        'created_at' => Mage::getSingleton('core/date')->timestamp(time()),
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                    ))->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                    $emailErrorFlag = true;
                }
                $options['email_sent'] = $_emailSent;
            }

            $item->setProductOptions($options);
            $item->save();

            if (true === $codeErrorFlag) {
                $url = Mage::getSingleton('adminhtml/url')->getUrl('aw_giftcard_admin/adminhtml_giftcard');
                $message = Mage::helper('aw_giftcard')->__(
                    'Some of Gift Cards for order #%s were not generated properly.'
                    . 'You can create Gift Card manually <a href="%s">here</a>.', $order->getIncrementId(), $url
                );
                Mage::getSingleton('adminhtml/session')->addError($message);
            }

            if (true === $emailErrorFlag) {
                $message = Mage::helper('aw_giftcard')->__(
                    'An error occurred while sending email'
                    . 'to recipient for order #%s.', $order->getIncrementId()
                );
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }
        return $this;
    }

    protected function _createGiftcard($item, $order, $balance, $websiteId)
    {
        $giftCardModel = Mage::getModel('aw_giftcard/giftcard');
        $giftCardModel
            ->setWebsiteId($websiteId)
            ->setStatus(AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::ENABLED_VALUE)
            ->setBalance($balance)
            ->setOrder($order)
        ;

        $expireAfter = 0;
        if ($item->getProductOptionByCode('aw_gc_expire_at')) {
            $expireAfter = $item->getProductOptionByCode('aw_gc_expire_at');
        }

        $expireAt = null;
        if ($expireAfter > 0) {
            $expireAt = Mage::app()->getLocale()
                ->date()
                ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
                ->addDay($expireAfter)
                ->toString(Varien_Date::DATE_INTERNAL_FORMAT)
            ;
        }
        $giftCardModel
            ->setExpireAt($expireAt)
            ->save()
        ;
        return $giftCardModel;
    }

    public function salesOrderCreditmemoRefund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        foreach ($creditmemo->getAllItems() as $item) {
            $_needCardToRefund = $item->getQty();
            if ($item->getOrderItem()->getProductType() != AW_Giftcard_Model_Catalog_Product_Type_Giftcard::TYPE_CODE
                && $_needCardToRefund > 0
            ) {
                continue;
            }

            $codes = $item->getOrderItem()->getProductOptionByCode('aw_gc_created_codes');
            if (null !== $codes && is_array($codes)) {
                $giftcards = $this->_getValidGiftcardToRefund($codes, $_needCardToRefund, $item);
                foreach ($giftcards as $giftcard) {
                    $giftcard
                        ->setStatus(AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::DISABLED_VALUE)
                        ->setState(AW_Giftcard_Model_Source_Giftcard_Status::REFUNDED_VALUE)
                        ->save()
                    ;
                }
            }
        }
        return $this;
    }

    public function salesOrderItemCancel(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        $_needCardToRefund = $item->getQtyToCancel();
        if ($item->getProductType() != AW_Giftcard_Model_Catalog_Product_Type_Giftcard::TYPE_CODE
            && $_needCardToRefund > 0
        ) {
            return $this;
        }

        $codes = $item->getProductOptionByCode('aw_gc_created_codes');
        if (null !== $codes && is_array($codes)) {
            $giftcards = $this->_getValidGiftcardToRefund($codes, $_needCardToRefund, $item);
            foreach ($giftcards as $giftcard) {
                $giftcard
                    ->setStatus(AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::DISABLED_VALUE)
                    ->setState(AW_Giftcard_Model_Source_Giftcard_Status::REFUNDED_VALUE)
                    ->save()
                ;
            }
        }
        return $this;
    }

    protected function _getValidGiftcardToRefund(array $codes, $needToRefund, $item)
    {
        $giftcards = array();
        $errors = array();
        foreach ($codes as $code) {
            $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->loadByCode($code);
            if (null === $giftcardModel->getId()) {
                $errors[] = Mage::helper('aw_giftcard')->__(
                    'Unable to refund card "%s" due to it\'s removal', Mage::helper('core')->escapeHtml($code)
                );
                continue;
            }

            if ($giftcardModel->getState() == AW_Giftcard_Model_Source_Giftcard_Status::REFUNDED_VALUE) {
                continue;
            }

            if ($giftcardModel->getState() == AW_Giftcard_Model_Source_Giftcard_Status::AVAILABLE_VALUE
                && $item->getBasePrice() > $giftcardModel->getBalance()
            ) {
                $errors[] = Mage::helper('aw_giftcard')->__(
                    'Unable to refund card "%s" due to it is already used',
                    Mage::helper('core')->escapeHtml($code)
                );
                continue;
            }

            if ($giftcardModel->getState() != AW_Giftcard_Model_Source_Giftcard_Status::AVAILABLE_VALUE) {
                $stateLabel = strtolower(
                    Mage::getModel('aw_giftcard/source_giftcard_status')->getOptionByValue($giftcardModel->getState())
                );
                if ($giftcardModel->getState() == AW_Giftcard_Model_Source_Giftcard_Status::USED_VALUE) {
                    $stateLabel = "out of balance";
                }
                $errors[] = Mage::helper('aw_giftcard')->__(
                    'Unable to refund card "%s" due to it is already %s',
                    Mage::helper('core')->escapeHtml($code),
                    $stateLabel
                );
            } else {
                array_push($giftcards, $giftcardModel);
            }

            if (count($giftcards) == $needToRefund) {
                break;
            }
        }
        if (count($errors) > 0 && count($giftcards) < $needToRefund) {
            $lastMessage = $errors[count($errors)-1];
            unset($errors[count($errors)-1]);
            foreach ($errors as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }
            throw new Mage_Core_Exception($lastMessage);
        }
        return $giftcards;
    }
}