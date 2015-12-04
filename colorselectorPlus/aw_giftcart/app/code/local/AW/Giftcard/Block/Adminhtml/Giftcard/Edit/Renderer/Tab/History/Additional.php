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

class AW_Giftcard_Block_Adminhtml_Giftcard_Edit_Renderer_Tab_History_Additional
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        if (null === $row->getData('additional_info')) {
            return '';
        }

        $addition = $row->getData('additional_info');
        if (!is_array($addition)
            || !array_key_exists('message_type', $addition)
            || !array_key_exists('message_data', $addition)
        ) {
            return '';
        }

        $messageLabel = Mage::getModel('aw_giftcard/source_giftcard_history_action')
            ->getMessageLabelByType($addition['message_type'])
        ;
        return Mage::helper('aw_giftcard')->__($messageLabel, $addition['message_data']);
    }
}