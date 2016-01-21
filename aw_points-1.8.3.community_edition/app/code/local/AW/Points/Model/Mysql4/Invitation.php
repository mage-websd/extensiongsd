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


class AW_Points_Model_Mysql4_Invitation extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('points/invitation', 'invitation_id');
        $this->_read = $this->_getReadAdapter();
    }

    /**
     * Get entity id from database by email address
     *
     * @param AW_Points_Model_Invitation $invitation
     * @param string $emailAddress
     * @param int $status
     *
     * @return integer
     */
    public function loadByEmailAndStatus($invitation, $emailAddress, $status)
    {
        $select = $this->_read->select()
            ->from($this->getTable('points/invitation'))
            ->where("email=? ", $emailAddress)
            ->where("status=? ", $status);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);
        return $this;
    }

    /**
     * Get entity id from database by email address
     *
     * @param AW_Points_Model_Invitation $invitation
     * @param string $emailAddress
     * @param int $storeId
     *
     * @return AW_Points_Model_Mysql4_Invitation
     */
    public function loadByEmailAndStore($invitation, $emailAddress, $storeId)
    {
        $select = $this->_read->select()
            ->from($this->getTable('points/invitation'))
            ->where("email=? ", $emailAddress)
            ->where("store_id=? ", $storeId);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);
        return $this;
    }

    /**
     * Load invitation data  from DB by email
     *
     * @param AW_Points_Model_Invitation $invitation
     * @param string $emailAddress
     *
     * @return AW_Points_Model_Mysql4_Invitation
     */
    public function loadByEmail($invitation, $emailAddress)
    {
        $select = $this->_read->select()
            ->from($this->getTable('points/invitation'))
            ->where('email=?', $emailAddress);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);
        return $this;
    }

    /**
     * Load invitation data  from DB by protection code
     *
     * @param AW_Points_Model_Invitation $invitation
     * @param string $protectionCode
     *
     * @return array
     */
    public function loadByProtectionCode($invitation, $protectionCode)
    {
        $select = $this->_read->select()
            ->from($this->getTable('points/invitation'))
            ->where('protection_code=?', $protectionCode);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);
        return $this;
    }

    /**
     * Load invitation data  from DB by customer and emailAddress
     *
     *
     * @param AW_Points_Model_Invitation $invitation
     * @param Mage_Customer_Model_Customer $customer
     * @param string $emailAddress
     *
     * @return AW_Points_Model_Mysql4_Invitation
     */
    public function loadByCustomerAndEmail($invitation, $customer, $emailAddress)
    {
        $select = $this->_read->select()
            ->from($this->getTable('points/invitation'))
            ->where("email=? ", $emailAddress)
            ->where("customer_id=?", $customer->getId());

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);
        return $this;
    }

    /**
     * Load invitation data  from DB by referral id
     *
     * @param AW_Points_Model_Invitation $invitation
     * @param int $referralId
     *
     * @return AW_Points_Model_Mysql4_Invitation
     */
    public function loadByReferralId($invitation, $referralId)
    {
        $select = $this->_read->select()
            ->from($this->getTable('points/invitation'))
            ->where("referral_id=?", $referralId);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);
        return $this;
    }
}