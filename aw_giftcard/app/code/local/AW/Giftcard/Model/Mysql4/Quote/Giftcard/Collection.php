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

class AW_Giftcard_Model_Mysql4_Quote_Giftcard_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_giftcard/quote_giftcard');
    }


    public function setFilterByQuoteId($quoteId)
    {
        $this->getSelect()->where('quote_entity_id = ?', $quoteId);
        return $this;
    }

    public function setFilterByGiftcardId($giftcardId)
    {
        $this->getSelect()->where('giftcard_id = ?', $giftcardId);
        return $this;
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
}