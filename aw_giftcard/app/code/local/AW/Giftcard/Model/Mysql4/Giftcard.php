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

class AW_Giftcard_Model_Mysql4_Giftcard extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('aw_giftcard/giftcard', 'entity_id');
    }

    public function isExistCode($code)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select
            ->from($this->getMainTable(), 'code')
            ->where('code = ?', $code)
        ;

        if ($read->fetchOne($select) === false) {
            return false;
        }
        return true;
    }

    public function removeTotals(AW_Giftcard_Model_Giftcard $giftcardModel)
    {
        $write = $this->_getWriteAdapter();
        $write->query(
            "DELETE FROM {$this->getTable('aw_giftcard/history')} "
            . "WHERE giftcard_id = {$giftcardModel->getId()}"
        );
        $write->query(
            "DELETE FROM {$this->getTable('aw_giftcard/quote_giftcard')} "
            . "WHERE giftcard_id = {$giftcardModel->getId()}"
        );
        $write->query(
            "DELETE FROM {$this->getTable('aw_giftcard/order_invoice_giftcard')} "
            . "WHERE giftcard_id = {$giftcardModel->getId()}"
        );
        $write->query(
            "DELETE FROM {$this->getTable('aw_giftcard/order_creditmemo_giftcard')} "
            . "WHERE giftcard_id = {$giftcardModel->getId()}"
        );
        return $this;
    }
}