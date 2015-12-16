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
        echo '<h1>------update state</h1>';
        $giftcardCollection = Mage::getModel('aw_giftcard/giftcard')->getCollection();
        $giftcardCollection
            ->addFieldToFilter('state', array("neq" => AW_Giftcard_Model_Source_Giftcard_Status::EXPIRED_VALUE))
        ;
        foreach ($giftcardCollection as $giftcardModel) {
            $giftcardModel->save();
        }
        return $this;
    }

    public function sendEmail()
    {
        $currentDate = Mage::getSingleton('core/date')->timestamp(time());
        $emailQueue = Mage::getModel('aw_giftcard/emailqueue')->getCollection();
        foreach ($emailQueue as $item) {
            if($item->getData('process_at')) {
                continue;
            }
            $schedule = $item->getData('schedule');
            if($currentDate >= strtotime($schedule)) {
                $templateData = unserialize($item->getData('template_data'));
                $store = unserialize($item->getData('store'));
                $emailTemplate = Mage::getModel('aw_giftcard/email_template');
                $emailTemplate->prepareEmailAndSend($templateData, $store);
                if ($emailTemplate->getSentSuccess()) {
                    $templateData['email_sent'] = AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::ENABLED_LABEL;

                    $order = $item->getData('order_id');
                    $order = Mage::getModel('sales/order')->load($order);
                    $itemProduct = $item->getData('item');
                    $itemProducts = $order->getAllVisibleItems();
                    foreach ($itemProducts as $i) {
                        if($itemProduct == $i->getProductId()) {
                            $i->setProductOptions($templateData);
                            $i->save();
                            break;
                        }
                    }
                    $item->setData('process_at',$currentDate);
                    $item->save();
                }
            }
        }
    }
}