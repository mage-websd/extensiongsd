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

class AW_Giftcard_Model_Mysql4_Order_Invoice_Giftcard_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_giftcard/order_invoice_giftcard');
    }

    public function joinGiftCardTable()
    {
        $this->getSelect()
            ->joinLeft(
                array(
                     'giftcard' => $this->getTable('aw_giftcard/giftcard')
                ),
                'main_table.giftcard_id = giftcard.entity_id',
                array(
                     'code'         => 'giftcard.code',
                     'card_balance' => 'giftcard.balance'
                )
            )
        ;
        return $this;
    }

    public function groupBy($columnName)
    {
        $this
            ->getSelect()
            ->group($columnName)
        ;
        return $this;
    }

    public function setFilterByInvoiceIds(array $invoiceIds)
    {
        $this->getSelect()->where('invoice_entity_id IN (?)', $invoiceIds);
        return $this;
    }

    public function setFilterByInvoiceId($invoiceId)
    {
        $this->getSelect()->where('invoice_entity_id = ?', $invoiceId);
        return $this;
    }

    public function setFilterByCardId($giftcardId)
    {
        $this->getSelect()->where('giftcard_id = ?', $giftcardId);
        return $this;
    }

    public function addSumBaseAmountToFilter()
    {
        $this->addExpressionFieldToSelect(
            'base_giftcard_amount',
            'SUM({{base_giftcard_amount}})',
            'base_giftcard_amount'
        );
        return $this;
    }

    public function addSumAmountToFilter()
    {
        $this->addExpressionFieldToSelect(
            'giftcard_amount',
            'SUM({{giftcard_amount}})',
            'giftcard_amount'
        );
        return $this;
    }
}