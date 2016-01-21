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


class AW_Points_Model_Actions_InviteeRegister extends AW_Points_Model_Actions_Abstract
{
    protected $_action = 'invitee_registered';
    protected $_comment = 'Reward for registration of invited user %s';

    protected function _applyLimitations($amount)
    {
        $pointLimitForAction = Mage::helper('points/config')->getLimitPointsOfInvitationForDay();

        $collection = Mage::getModel('points/transaction')
            ->getCollection()
            ->addFieldToFilter('summary_id', $this->getSummary()->getId())
            ->addFieldToFilter('action', $this->getAction())
            ->limitByDay(Mage::getModel('core/date')->gmtTimestamp());

        /* Current summ getting */
        $summ = 0;
        foreach ($collection as $transaction) {
            $summ += $transaction->getBalanceChange();
        }
        return parent::_applyLimitations($this->_calculateNewAmount($summ, $amount, $pointLimitForAction));
    }

    public function getCommentHtml($area = self::ADMIN)
    {
        return $this->_translateComment($this->_transaction->getComment());
    }

    public function getComment()
    {
        if (isset($this->_commentParams['referral_name'])) {
            return array($this->_comment, $this->_commentParams['referral_name']);
        }
        return $this->_comment;
    }
}
