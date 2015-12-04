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

class AW_Giftcard_Block_Frontend_Sales_Order_Totals_Giftcard extends Mage_Core_Block_Template
{
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function initTotals()
    {
        if ($this->getSource() instanceof Mage_Sales_Model_Order && null === $this->getSource()->getAwGiftCards()) {
            $quoteGiftcardsItems = Mage::helper('aw_giftcard/totals')
                ->getQuoteGiftCards($this->getSource()->getQuoteId())
            ;

            if (count($quoteGiftcardsItems) > 0) {
                $this->getSource()->setAwGiftCards($quoteGiftcardsItems);
            }
        }

        if ($this->getSource()->getAwGiftCards() && count($this->getSource()->getAwGiftCards()) > 0) {
            foreach ($this->getSource()->getAwGiftCards() as $card) {
                $this->getParentBlock()->addTotal(
                    new Varien_Object(
                        array(
                             'code'   => 'aw_giftcard_' . $card->getGiftcardId(),
                             'strong' => false,
                             'label'  => $this->__('Gift Card (%s)', $card->getCode()),
                             'value'  => -$card->getGiftcardAmount(),
                        )
                    ),
                    'tax'
                );
            }
        }
        return $this;
    }
}