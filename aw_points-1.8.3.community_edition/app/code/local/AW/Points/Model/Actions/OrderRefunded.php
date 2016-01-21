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


class AW_Points_Model_Actions_OrderRefunded extends AW_Points_Model_Actions_OrderInvoiced
{
    protected $_action = 'order_refunded';
    protected $_comment = 'Refund of %s item(s) related to order %s';
    protected $_commentPatterns = array(
        "/^Refund\sof\s\w+\sitem\(s\)\srelated\sto\sorder\s#\d+/i" =>
            'Refund of %s item(s) related to order %s',
        "/^Points\scancelled\sbecause\sof\srefund\sof\sorder\s#\d+/" =>
            'Points cancelled because of refund of order %s',
        "/^Points\scancelled\sbecause\sof\srefund\sof\s\d+\sitem\(s\)\srelated\sto\sorder\s#\d+/i" =>
            'Points cancelled because of refund of %s item(s) related to order %s',
        "/^Points\scancelled\sbecause\sof\scancellation\sof\sorder\s#\d+/" =>
            'Points cancelled because of cancellation of order %s',
        "/^Points\scancelled\sbecause\sof\sreferral\sorder\srefund/" =>
            'Points cancelled because of referral order refund',
        "/^Cancelation\sof\sorder\s#\d+/" =>
            'Cancelation of order #%s'
    );

    public function getComment()
    {
        if (isset($this->_commentParams['comment'])) {
            return $this->_commentParams['comment'];
        }
        return $this->_comment;
    }

    public function getCommentHtml($area = self::ADMIN)
    {
        $_comment = $this->_comment;
        foreach ($this->_commentPatterns as $pattern => $commentPattern) {
            if (preg_match($pattern, $this->_transaction->getComment())) {
                $_comment = $commentPattern;
                break;
            }
        }
        $comment = $this->_translatePattern(
            $this->_transaction->getComment(),
            $_comment
        );
        preg_match_all("#\#(\S{4,})#isu", $comment, $matches);

        if (!$this->_transaction) {
            return;
        }

        $patterns = $replacements = array();
        if (isset($matches[1]) && !empty($matches[1])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($matches[1][$i]);
                $patterns[] = "#\#{$matches[1][$i]}#isu";
                if (!$order->getId()) {
                    $replacements[] = "#{$matches[1][$i]}";
                    continue;
                }
                $replacements[] = "{$this->_getLinkHtml($order, $area)}";
            }
        }
        return preg_replace($patterns, $replacements, $comment);
    }

    protected function _getLinkHtml($order, $area)
    {
        if ($area == self::ADMIN) {
            $orderUrl = Mage::getModel('adminhtml/url')->getUrl(
                'adminhtml/sales_order/view/',
                array('order_id' => $order->getId())
            );
        } else {
            $orderUrl = Mage::getUrl(
                'sales/order/view/',
                array('order_id' => $order->getId())
            );
        }
        return "<a href='{$orderUrl}'>#{$order->getIncrementId()}</a>";
    }
}