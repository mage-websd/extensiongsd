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


class AW_Points_Model_Coupon extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('points/coupon');
    }

    /**
     * Load coupon by code
     *
     * @param   string $couponCode
     * @return  AW_Points_Model_Coupon
     */
    public function LoadByCouponCode($couponCode)
    {
        $this->_getResource()->LoadByCouponCode($this, $couponCode);
        return $this;
    }

    /**
     *    coupon is expired
     *    to_date > current_date
     */
    public function isExpired()
    {
        $toDate = $this->getData('to_date');
        if ($toDate) {
            $today = Mage::app()->getLocale()->date();
            if ($toDate < $today->toString(Varien_Date::DATE_INTERNAL_FORMAT)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     *   from_date >= current_date
     *
     *  @return bool
     */
    public function isStarted()
    {
        $fromDate = $this->getData('from_date');
        if ($fromDate) {
            $today = Mage::app()->getLocale()->date();
            if ($today->toString(Varien_Date::DATE_INTERNAL_FORMAT) >= $fromDate) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check customer's group
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return bool
     */
    public function validateCustomerGroup($customer)
    {
        if (!$this->getId() || !$customer->getId()) {
            return FALSE;
        }

        $customerGroupId = $customer->getData('group_id');
        $couponGroupIds = $this->getData('customer_group_ids');
        $result = in_array($customerGroupId, $couponGroupIds);
        return $result;
    }

    /**
     *   coupon can be activated on current website
     *   @return bool
     */

    public function validateWebsite()
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $couponWebsiteIds = $this->getData('website_ids');

        $result = in_array($websiteId, $couponWebsiteIds);
        return $result;
    }

    public function activate()
    {
        $activationCnt = (int) $this->getData('activation_cnt') + 1;
        $this
            ->setData('activation_cnt', $activationCnt)
            ->save();
    }
}