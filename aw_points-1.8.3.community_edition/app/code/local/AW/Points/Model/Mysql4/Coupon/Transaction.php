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


class AW_Points_Model_Mysql4_Coupon_Transaction extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('points/coupon_transaction', 'id');
    }

    public function LoadByCouponIdCustomerId($couponTransaction, $couponId, $customerId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('points/coupon_transaction'))
            ->where('coupon_id=?', $couponId)
            ->where('customer_id=?', $customerId)
            ->limit(1);
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $couponTransaction->addData($data);
        }
        return $this;
    }

}
