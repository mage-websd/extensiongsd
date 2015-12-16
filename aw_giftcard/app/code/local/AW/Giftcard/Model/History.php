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

class AW_Giftcard_Model_History extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_giftcard/history');
    }

    public function registerAction($actionType, AW_Giftcard_Model_Giftcard $giftcardModel)
    {
        $info = array(
            'message_type' => AW_Giftcard_Model_Source_Giftcard_History_Action::BY_ADMIN_MESSAGE_VALUE,
            'message_data' => $this->_getCurrentAdminUserName()
        );

        if (null !== $giftcardModel->getOrder()) {
            $info = array(
                'message_type' => AW_Giftcard_Model_Source_Giftcard_History_Action::BY_ORDER_MESSAGE_VALUE,
                'message_data' => $giftcardModel->getOrder()->getIncrementId()
            );
        }

        if (null !== $giftcardModel->getCreditmemo()) {
            $orderIncrementId = $giftcardModel->getCreditmemo()->getIncrementId();
            if ($giftcardModel->getCreditmemo() instanceof Mage_Sales_Model_Order_Creditmemo) {
                $orderIncrementId = $giftcardModel->getCreditmemo()->getOrder()->getIncrementId();
            }
            $info = array(
                'message_type' => AW_Giftcard_Model_Source_Giftcard_History_Action::BY_CREDITMEMO_MESSAGE_VALUE,
                'message_data' => $orderIncrementId
            );
        }

        $_balanceDelta = $giftcardModel->getBalance();
        if (!$giftcardModel->getIsNew() && null !== $giftcardModel->getOrigData('balance')) {
            $_balanceDelta = $giftcardModel->getBalance() - $giftcardModel->getOrigData('balance');
        }

        $this
            ->setGiftcardId($giftcardModel->getId())
            ->setAction($actionType)
            ->setBalanceDelta($_balanceDelta)
            ->setBalanceAmount($giftcardModel->getBalance())
            ->setAdditionalInfo($info)
            ->save()
        ;
        return $this;
    }

    protected function _getCurrentAdminUserName()
    {
        if ($user = Mage::getSingleton('admin/session')->getUser()) {
            if ($username = $user->getUsername()) {
                return $username;
            }
        }
        return null;
    }
}