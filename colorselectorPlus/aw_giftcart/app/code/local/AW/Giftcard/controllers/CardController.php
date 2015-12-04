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

class AW_Giftcard_CardController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (!$this->_validateUser()) {
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Gift Card'));
        $this->renderLayout();
    }

    protected function _validateUser()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return false;
        }
        return true;
    }

    public function checkAction()
    {
        if (!$this->_validateUser()) {
            return;
        }

        try {
            $this->_initCard();
            if ($this->getRequest()->getParam('is-gc-redeem', null)) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                Mage::registry('current_giftcard')->redeemToStorecredit($customerId);
                Mage::getSingleton('customer/session')->addSuccess(
                    $this->__(
                        'Gift Card "%s" has been redeemed to Store Credit.',
                        Mage::helper('core')->escapeHtml(Mage::registry('current_giftcard')->getCode())
                    )
                );
            }
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
        }
        $this->_forward('index');
    }

    public function checkMobileAjaxAction()
    {
        try {
            $giftcardModel = $this->_initCard();
            Mage::getSingleton('checkout/session')->setCurrentGiftCard($giftcardModel);
        } catch (Exception $e) {
            Mage::register('aw_gc_check_error', $this->__($e->getMessage()));
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _initCard()
    {
        $giftcardCode = $this->getRequest()->getParam('giftcard_code', null);
        if (null === $giftcardCode) {
            throw new Exception($this->__('Please enter gift card code.'));
        }

        $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->loadByCode(trim($giftcardCode));
        if (null === $giftcardModel->getId()) {
            throw new Exception(
                $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftcardCode))
            );
        }

        if ($giftcardModel->isValidForRedeem()) {
            Mage::register('current_giftcard', $giftcardModel, true);
        }
        return $giftcardModel;
    }

    public function applyAction()
    {
        try {
            $giftcardModel = $this->_initCard();
            if (!$this->getRequest()->getParam('status_flag', null)) {
                Mage::helper('aw_giftcard/totals')->addCardToQuote($giftcardModel);

                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" has been added.', Mage::helper('core')->escapeHtml($giftcardModel->getCode()))
                );
            } else {
                Mage::getSingleton('checkout/session')->setCurrentGiftCard($giftcardModel);
            }

        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__($e->getMessage()));
        }
        $this->_redirect('checkout/cart');
    }

    public function applyMobileAjaxAction()
    {
        try {
            $giftcardModel = $this->_initCard();
        } catch (Mage_Core_Exception $e) {
            return $this->getResponse()->setBody('');
        } catch (Exception $e) {
            if (0 == $this->getRequest()->getParam('status_flag', null)) {
                Mage::getSingleton('checkout/session')->addError($this->__($e->getMessage()));
                $this->_forward('updatePost', 'cart', 'awmobile', array());
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }
            return;
        }

        if (!$this->getRequest()->getParam('status_flag', null)) {
            try {
                Mage::helper('aw_giftcard/totals')->addCardToQuote($giftcardModel);
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" has been added.', Mage::helper('core')->escapeHtml($giftcardModel->getCode()))
                );
                $quote = Mage::helper('aw_giftcard/totals')->getQuote();
                $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                $this->_forward('updatePost', 'cart', 'awmobile', array());
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($this->__($e->getMessage()));
                $this->_forward('updatePost', 'cart', 'awmobile', array());
            }
        } else {
            Mage::getSingleton('checkout/session')->setCurrentGiftCard($giftcardModel);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function removeAction()
    {
        if ($giftcardCode = $this->getRequest()->getParam('giftcard_code', null)) {
            $giftcardCode = base64_decode($giftcardCode);
            try {
                Mage::helper('aw_giftcard/totals')->removeCardFromQuote(trim($giftcardCode));
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" has been removed.', Mage::helper('core')->escapeHtml($giftcardCode))
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot remove gift card.'));
            }

        }
        $this->_redirect('checkout/cart');
    }

    public function removeMobileAjaxAction()
    {
        if ($giftcardCode = $this->getRequest()->getParam('giftcard_code', null)) {
            $giftcardCode = base64_decode($giftcardCode);
            try {
                Mage::helper('aw_giftcard/totals')->removeCardFromQuote(trim($giftcardCode));
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" has been removed.', Mage::helper('core')->escapeHtml($giftcardCode))
                );
                $quote = Mage::helper('aw_giftcard/totals')->getQuote();
                $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                $this->_forward('updatePost', 'cart', 'awmobile', array());
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot remove gift card.'));
                $this->_forward('updatePost', 'cart', 'awmobile', array());
            }

        }
        $this->loadLayout();
        $this->renderLayout();
    }
}