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

class AW_Giftcard_Block_Frontend_Checkout_Cart_Total extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'aw_giftcard/cart/total.phtml';
    protected $_giftCards = null;

    public function getAwGiftCards()
    {
        if (null === $this->_giftCards) {
            $this->_giftCards = $this->getTotal()->getAwGiftCards();
            if (null === $this->_giftCards) {
                $this->_giftCards = Mage::helper('aw_giftcard/totals')->getQuoteGiftCards();
            }
        }
        return $this->_giftCards;
    }
}