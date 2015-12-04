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

class AW_Giftcard_Model_Cron
{
    public function updateStates()
    {
        $giftcardCollection = Mage::getModel('aw_giftcard/giftcard')->getCollection();
        $giftcardCollection
            ->addFieldToFilter('state', array("neq" => AW_Giftcard_Model_Source_Giftcard_Status::EXPIRED_VALUE))
        ;
        foreach ($giftcardCollection as $giftcardModel) {
            $giftcardModel->save();
        }
        return $this;
    }
}