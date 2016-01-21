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


class AW_Points_Model_Actions_OrderInvoiced extends AW_Points_Model_Actions_Abstract
{
    protected $_action = 'order_invoiced';
    protected $_comment = 'Reward for order #%s';
    protected $_commentHtml = 'Reward for order %s#%s%s';

    public function getComment()
    {
        if (isset($this->_commentParams['order_increment_id'])) {
            return sprintf($this->_comment, $this->_commentParams['order_increment_id']);
        }
        return $this->_comment;
    }

    public function getCommentHtml($area = self::ADMIN)
    {
        if (!$this->_transaction) {
            return;
        }
        $orderIncrementId = substr(
            $this->_transaction->getComment(), strpos($this->_transaction->getComment(), '#') + 1
        );
        if (!$orderIncrementId) {
            return;
        }
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        if ($area == self::ADMIN) {
            $orderUrl = Mage::getModel('adminhtml/url')->getUrl(
                'adminhtml/sales_order/view/', array('order_id' => $order->getId())
            );
        } else {
            $orderUrl = Mage::getUrl('sales/order/view/', array('order_id' => $order->getId()));
        }
        return sprintf(
            $this->_getHelper()->__($this->_commentHtml), '<a href="' . $orderUrl . '">', $order->getIncrementId(), '</a>'
        );
    }
}
