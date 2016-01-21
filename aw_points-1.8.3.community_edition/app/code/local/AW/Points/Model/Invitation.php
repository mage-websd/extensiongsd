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


class AW_Points_Model_Invitation extends Mage_Core_Model_Abstract
{
    const INVITATION_NEW = 0;
    const INVITATION_SENT = 1;
    const INVITATION_ACCEPTED = 2;
    const INVITEE_IS_CUSTOMER = 3;

    const INVITATION_ACCEPTED_FROM_OTHER = 4;
    const INVITEE_IS_CUSTOMER_FROM_OTHER = 5;

    const INVITEE_EXIST = 10;
    const REGISTERED_CUSTOMER = 20;

    const XML_PATH_EMAIL_TEMPLATE = 'points/notifications/template';
    const XML_PATH_EMAIL_IDENTITY = 'points/notifications/identity';

    public function _construct()
    {
        parent::_construct();
        $this->_init('points/invitation');
    }

    /**
     * Send email to invitee
     *
     * @param string $emailAddress
     * @param Mage_Core_Model_Store $store
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return boolean
     */
    public function sendEmail($emailAddress, $store, $customer)
    {
        $mail = Mage::getModel('core/email_template');

        /* Magento 1.3 stub. */
        if (Mage::helper('points')->magentoLess14()) {
            $store->setFrontendName($store->getGroup()->getName());
        }
        /* Magento 1.3 stub ends */

        try {
            $mail
                ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getStoreId()))
                ->sendTransactional(
                    $store->getConfig(self::XML_PATH_EMAIL_TEMPLATE),
                    $store->getConfig(self::XML_PATH_EMAIL_IDENTITY),
                    $this->getEmail(),
                    null,
                    array(
                         'url'      => $this->prepareUrl($customer, $emailAddress, $store),
                         'message'  => $this->getMessage(),
                         'store'    => $store,
                         'customer' => $customer,
                    )
                )
            ;
            if ($mail->getSentSuccess()) {
                $this->setStatus(self::INVITATION_SENT)->setUpdateDate(true)->save();
                return true;
            }
        } catch (Exception $exc) {
            Mage::helper('awcore/logger')->log(
                $this,
                Mage::helper('points')->__('Error on saving invitation data for email: %s', $emailAddress),
                AW_Core_Model_Logger::LOG_SEVERITY_ERROR, $exc->getTraceAsString()
            );
        }

        return false;
    }

    /**
     * Saves new invitation data in DB and returns instance
     *
     * @param Mage_Customer_Model_Session $sessionData
     *
     * @return AW_Points_Model_Invitation
     */
    public function saveNewInvitation($sessionData)
    {
        $this->setData($sessionData->getData());
        $this->addData(
            array(
                 'protection_code' => md5(uniqid(microtime(), true)),
                 'status'          => self::INVITATION_NEW,
                 'date'            => $this->getResource()->formatDate(time()),
            )
        );
        $this->save();
        return $this;
    }

    /**
     * Get invitation accepted by customer
     *
     * @param string $customerEmail
     *
     * @return AW_Points_Model_Invitation
     */
    public function loadAcceptedByEmail($customerEmail)
    {
        $this->getResource()->loadByEmailAndStatus($this, $customerEmail, self::INVITATION_ACCEPTED);
        return $this;
    }

    /**
     * Update invitation with referral data
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function setCustomerAsReferral($customer)
    {
        $this->setSignupDate($this->getResource()->formatDate(time()))
            ->setReferralId($customer->getId())
            ->setStatus(self::INVITEE_IS_CUSTOMER)
            ->setUpdateDate(true)
            ->save()
        ;
    }

    /**
     * Prepare url to insert into invitation email
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $emailAddress
     * @param Mage_Core_Model_Store $store
     *
     * @return string
     */
    public function prepareUrl($customer, $emailAddress, $store)
    {
        $this->getResource()->loadByCustomerAndEmail($this, $customer, $emailAddress);
        $preparedUrl = Mage::getModel('core/url')
            ->setStore($store->getStoreId())
            ->getUrl(
                'points/invitation/createAccount/',
                array(
                     'invitation'    => Mage::helper('core')->urlEncode($this->getProtectionCode()),
                     '_store_to_url' => true,
                )
            );
        return $preparedUrl;
    }

    /**
     *  Validate secure code to check if invitation has been accepted
     *  and updates invitation status
     *
     * @param string $paramCodeToValidate
     *
     * @return void
     */
    public function validateSecureCode($paramCodeToValidate)
    {
        $codeToValidate = Mage::helper('core')->urlDecode($paramCodeToValidate);
        $this->getResource()->loadByProtectionCode($this, $codeToValidate);
        if ($this->getId() && $this->getStatus() == self::INVITATION_SENT) {
            $this->setStatus(self::INVITATION_ACCEPTED)->setUpdateDate(true)->save();

            $invitationCollection = Mage::getResourceModel('points/invitation_collection')
                ->addEmailFilter($this->getEmail())
            ;

            foreach ($invitationCollection as $invitation) {
                if ($invitation->getId() !== $this->getId()) {
                    $invitation->setStatus(self::INVITATION_ACCEPTED_FROM_OTHER)->setUpdateDate(true)->save();
                }
            }

        }
    }

    /**
     * Load invitation data from resource model by email
     *
     * @param string $subscriberEmail
     *
     * @return AW_Points_Model_Invitation
     */
    public function loadByEmail($subscriberEmail)
    {
        $this->getResource()->loadByEmail($this, $subscriberEmail);
        return $this;
    }

    /**
     * Load invitation data from resource model by email
     *
     * @param string $subscriberEmail
     * @param int    $storeId
     *
     * @return AW_Points_Model_Invitation
     */
    public function loadByEmailAndStore($subscriberEmail, $storeId)
    {
        $this->getResource()->loadByEmailAndStore($this, $subscriberEmail, $storeId);
        return $this;
    }

    /**
     * Load invitation data from resource model by referral id
     *
     * @param int $referralId
     *
     * @return AW_Points_Model_Invitation
     */
    public function loadByReferralId($referralId)
    {
        $this->getResource()->loadByReferralId($this, $referralId);
        return $this;
    }
}