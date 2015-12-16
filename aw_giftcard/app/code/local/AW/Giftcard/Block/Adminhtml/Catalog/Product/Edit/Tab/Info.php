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

class AW_Giftcard_Block_Adminhtml_Catalog_Product_Edit_Tab_Info extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        $this->setTemplate('aw_giftcard/catalog/product_edit_tab_giftcard.phtml');
        parent::_construct();
    }

    public function getTabLabel()
    {
        return $this->__('Gift Card Information');
    }

    public function getTabTitle()
    {
        return $this->__('Gift Card Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function isNew()
    {
        if (Mage::registry('product')->getId()) {
            return false;
        }
        return true;
    }

    public function getFieldPrefix()
    {
        return 'product';
    }

    public function getFieldValue($field)
    {
        if (!$this->isNew()) {
            return Mage::registry('product')->getDataUsingMethod($field);
        }
        return '';
    }

    public function getConfigExpireValue()
    {
        return Mage::helper('aw_giftcard/config')->getExpireValue();
    }

    public function getConfigIsAllowMessageValue()
    {
        return Mage::helper('aw_giftcard/config')->isAllowGiftMessage();
    }

    public function getConfigEmailTemplateValue()
    {
        return Mage::helper('aw_giftcard/config')->getEmailTemplate();
    }

    public function getOptionsYesno()
    {
        return Mage::getModel('aw_giftcard/source_product_attribute_option_yesno')->toOptionArray();
    }

    public function getEmailTemplates()
    {
        $result = array();
        $template = Mage::getModel('adminhtml/system_config_source_email_template');
        $template->setPath(AW_Giftcard_Model_Email_Template::DEFAULT_EMAIL_TEMPLATE_PATH);
        foreach ($template->toOptionArray() as $one) {
            $result[$one['value']] = $this->escapeHtml($one['label']);
        }
        return $result;
    }

    public function getTypes()
    {
        return Mage::getModel('aw_giftcard/source_product_attribute_giftcard_type')->toOptionArray();
    }
}