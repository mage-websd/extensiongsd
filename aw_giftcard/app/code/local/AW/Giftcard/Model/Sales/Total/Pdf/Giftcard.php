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

class AW_Giftcard_Model_Sales_Total_Pdf_Giftcard extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    public function getTotalsForDisplay()
    {
        $_result = array();
        if ($this->getSource()->getAwGiftCards() && count($this->getSource()->getAwGiftCards()) > 0) {
            $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
            $_cardTotals = array();
            foreach ($this->getSource()->getAwGiftCards() as $card) {
                $_cardTotals['aw_giftcard_' . $card->getGiftcardId()] = array (
                    'amount'    => '-' . $this->getOrder()->formatPriceTxt($card->getGiftcardAmount()),
                    'label'     => Mage::helper('aw_giftcard')->__('Gift Card (%s)', $card->getCode()),
                    'font_size' => $fontSize,
                );
            }
            $_result = $_cardTotals;
        }
        return $_result;
    }
}