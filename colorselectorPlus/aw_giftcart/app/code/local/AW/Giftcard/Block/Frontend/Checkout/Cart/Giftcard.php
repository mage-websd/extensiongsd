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

class AW_Giftcard_Block_Frontend_Checkout_Cart_Giftcard extends Mage_Checkout_Block_Cart_Abstract
{
    public function getTemplate()
    {
        return 'aw_giftcard/checkout/cart_giftcard.phtml';
    }

    public function getFormUrl()
    {
        return $this->getUrl(
            'awgiftcard/card/apply',
            array(
                 '_secure' => Mage::app()->getStore(true)->isCurrentlySecure()
            )
        );
    }

    public function isEEVersion()
    {
        return $this->helper('aw_giftcard')->isEEVersion();
    }
}