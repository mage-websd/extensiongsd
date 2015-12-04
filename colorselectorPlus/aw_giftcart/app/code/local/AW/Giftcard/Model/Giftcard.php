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

class AW_Giftcard_Model_Giftcard extends Mage_Core_Model_Abstract
{
    protected $_historyCollection = null;

    const CODE_GENERATION_ATTEMPTS = 1000;

    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_giftcard/giftcard');
    }

    public function loadByCode($code)
    {
        return $this->load($code, 'code');
    }

    protected function _beforeSave()
    {
        $_result = parent::_beforeSave();

        if (null === $this->getId() && $this->isExpired()) {
            throw new Mage_Core_Exception(
                Mage::helper('aw_giftcard')->__('Expiration date cannot be in the past.')
            );
        }

        $this->_attachState();

        if (null === $this->getId()) {
            $this->setIsNew(true);
            $attempt = 0;
            do {
                if ($attempt >= self::CODE_GENERATION_ATTEMPTS) {
                    Mage::throwException(
                        Mage::helper('aw_giftcard')->__(
                            'Unable to create giftcard code. Please check settings and try again.'
                        )
                    );
                }
                $code = $this->_generateCode();
                $attempt++;
            } while ($this->getResource()->isExistCode($code));

            $currentDate = Mage::app()->getLocale()
                ->date()
                ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
                ->toString(Varien_Date::DATE_INTERNAL_FORMAT)
            ;
            $this
                ->setCreatedAt($currentDate)
                ->setCode($code)
            ;
        }
        return $_result;
    }

    public function isValidForRedeem($storeId = null)
    {
        if ($this->getState() != AW_Giftcard_Model_Source_Giftcard_Status::AVAILABLE_VALUE) {
            if (null === $this->getState()) {
                throw new Exception(
                    Mage::helper('aw_giftcard')->__(
                        AW_Giftcard_Model_Source_Giftcard_Status::DEFAULT_ERROR_MESSAGE
                    )
                );
            } else {
                $_errorMessage = Mage::getModel('aw_giftcard/source_giftcard_status')
                    ->getErrorMessage($this->getState())
                ;
                throw new Exception(Mage::helper('aw_giftcard')->__($_errorMessage));
            }
        }

        if ($this->isExpired()) {
            throw new Exception(
                Mage::helper('aw_giftcard')->__(
                    AW_Giftcard_Model_Source_Giftcard_Status::EXPIRED_ERROR_MESSAGE
                )
            );
        }

        if ($this->getStatus() != AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::ENABLED_VALUE) {
            throw new Exception(
                Mage::helper('aw_giftcard')->__(
                    'Gift Card "%s" is not active.', Mage::helper('core')->escapeHtml($this->getCode())
                )
            );
        }

        $store = Mage::app()->getStore($storeId);
        $website = $store->getWebsite();
        if ($this->getWebsiteId() != $website->getId()) {
            throw new Exception(
                Mage::helper('aw_giftcard')->__(
                    AW_Giftcard_Model_Source_Giftcard_Status::DEFAULT_ERROR_MESSAGE
                )
            );
        }
        return true;
    }

    protected function _generateCode()
    {
        $website   = Mage::app()->getWebsite($this->getWebsiteId());
        $format    = str_split((string)Mage::helper('aw_giftcard/config')->getCouponCodeFormat($website));
        $length    = max(1, (int) Mage::helper('aw_giftcard/config')->getCouponCodeLength($website));
        $split     = max(0, (int) Mage::helper('aw_giftcard/config')->getCouponCodeDash($website));
        $suffix    = Mage::helper('aw_giftcard/config')->getCouponCodeSuffix($website);
        $prefix    = Mage::helper('aw_giftcard/config')->getCouponCodePrefix($website);
        $splitChar = (string) Mage::helper('aw_giftcard/config')->getCouponCodeSeparator($website);
        $code      = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $format[array_rand($format)];
            if ($split > 0 && ($i%$split) == 0 && $i != 0) {
                $char = "{$splitChar}{$char}";
            }
            $code .= $char;
        }

        $code = "{$prefix}{$code}{$suffix}";

        return $code;
    }

    public function isExpired()
    {
        $currentDate = Mage::app()->getLocale()->date(null, Varien_Date::DATE_INTERNAL_FORMAT, null, false);
        $expirationDate = Mage::app()->getLocale()
            ->date($this->getExpireAt(), Varien_Date::DATE_INTERNAL_FORMAT, null, false)
        ;
        if ($expirationDate < $currentDate) {
            return true;
        }
        return false;
    }

    protected function _attachState()
    {
        $state = $this->getState();
        if ((null === $state || $state == AW_Giftcard_Model_Source_Giftcard_Status::USED_VALUE && !$this->isUsed())
            || (!$this->isExpired() && $state == AW_Giftcard_Model_Source_Giftcard_Status::EXPIRED_VALUE)
        ) {
            $state = AW_Giftcard_Model_Source_Giftcard_Status::AVAILABLE_VALUE;
        }

        if ($this->isExpired() && $state != AW_Giftcard_Model_Source_Giftcard_Status::REFUNDED_VALUE) {
            $state = AW_Giftcard_Model_Source_Giftcard_Status::EXPIRED_VALUE;
        }

        if ($this->isUsed() && $state != AW_Giftcard_Model_Source_Giftcard_Status::REFUNDED_VALUE) {
            $state = AW_Giftcard_Model_Source_Giftcard_Status::USED_VALUE;
        }
        $this->setState($state);
        return $this;
    }

    protected function isUsed()
    {
        if ($this->getBalance() > 0) {
            return false;
        }
        return true;
    }

    protected function _afterSave()
    {
        $_result = parent::_afterSave();
        $this->_registerHistory();
        return $_result;
    }

    protected function _registerHistory()
    {
        if ($this->getIsNew()) {
            Mage::getModel('aw_giftcard/history')
                ->registerAction(AW_Giftcard_Model_Source_Giftcard_History_Action::CREATED_VALUE, $this)
            ;
        }

        if (!$this->getIsNew() && $this->getOrigData('balance') > $this->getBalance()
            && null !== $this->getOrder()
        ) {
            Mage::getModel('aw_giftcard/history')
                ->registerAction(AW_Giftcard_Model_Source_Giftcard_History_Action::USED_VALUE, $this)
            ;
        }

        if (!$this->getIsNew() && $this->getOrigData('balance') != $this->getBalance()) {
            if (null !== $this->getCreditmemo()) {
                Mage::getModel('aw_giftcard/history')
                    ->registerAction(AW_Giftcard_Model_Source_Giftcard_History_Action::REFUNDED_VALUE, $this)
                ;
            }

            if (null === $this->getCreditmemo() && null === $this->getOrder()) {
                Mage::getModel('aw_giftcard/history')
                    ->registerAction(AW_Giftcard_Model_Source_Giftcard_History_Action::UPDATED_VALUE, $this)
                ;
            }
        }

        if ($this->getOrigData('state') && $this->getState()
            == AW_Giftcard_Model_Source_Giftcard_Status::EXPIRED_VALUE
        ) {
            Mage::getModel('aw_giftcard/history')
                ->registerAction(AW_Giftcard_Model_Source_Giftcard_History_Action::EXPIRED_VALUE, $this)
            ;
        }

        if ($this->getIsRedeemed()) {
            Mage::getModel('aw_giftcard/history')
                ->registerAction(AW_Giftcard_Model_Source_Giftcard_History_Action::REDEEMED_VALUE, $this);
        }
        return $this;
    }

    public function getHistoryCollection()
    {
        if (null === $this->_historyCollection) {
            $collection = Mage::getModel('aw_giftcard/history')
                ->getCollection()
                ->addFieldToFilter('giftcard_id', $this->getId())
            ;
            $this->_historyCollection = $collection;
        }
        return $this->_historyCollection;
    }

    public function _afterLoad()
    {
        $this->_attachStateText();
        return parent::_afterLoad();
    }

    protected function _attachStateText()
    {
        $stateLabel = Mage::getModel('aw_giftcard/source_giftcard_status')->getOptionByValue($this->getState());
        if (null !== $stateLabel) {
            $this->setStateText($stateLabel);
        }
        return $this;
    }

    public function delete()
    {
        $this->_getResource()->removeTotals($this);
        return parent::delete();
    }

    public function redeemToStorecredit($customerId)
    {
        $storeCredit = Mage::getModel('aw_storecredit/storecredit')->loadByCustomerId($customerId);
        $giftCardBalance = $this->getBalance();
        try{
            $storeCredit
                ->setBalance($storeCredit->getBalance() + $giftCardBalance)
                ->setTotalBalance($storeCredit->getTotalBalance() + $giftCardBalance)
                ->setCustomerId($customerId)
                ->setRedeemedFromGiftCard(true)
                ->save();

            $this
                ->setIsRedeemed(true)
                ->setBalance(0)
                ->save();
        }
        catch (Exception $e) {
            throw new Exception($this->__($e->getMessage()));
        }

    }
}
