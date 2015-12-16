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

class AW_Giftcard_Block_Adminhtml_Giftcard_Edit_Tab_Information extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_giftcard/giftcard/edit_tab_info.phtml');
    }

    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_info_');

        $giftcardModel = Mage::registry('current_giftcard');
        $fieldset = $form->addFieldset('fieldset', array('legend' => $this->__('Information')));

        if ($giftcardModel->getId()) {
            $fieldset->addField('code', 'label', array(
                'name'      => 'code',
                'label'     => $this->__('Gift Card Code'),
                'title'     => $this->__('Gift Card Code')
            ));

            $fieldset->addField('state_text', 'label', array(
                'name'      => 'state_text',
                'label'     => $this->__('Status'),
                'title'     => $this->__('Status')
            ));
        }

        $fieldset->addField('status', 'select', array(
             'label'     => $this->__('Active'),
             'title'     => $this->__('Active'),
             'name'      => 'status',
             'required'  => true,
             'options'   => Mage::getModel('aw_giftcard/source_product_attribute_option_yesno')->toOptionArray(),
        ));

        if (!$giftcardModel->getId()) {
            $giftcardModel->setData('status', AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::ENABLED_VALUE);
        }

        $fieldset->addField('website_id', 'select', array(
          'name'      => 'website_id',
          'label'     => $this->__('Website'),
          'title'     => $this->__('Website'),
          'required'  => true,
          'values'    => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(true),
        ));

        $fieldset->addType('price', 'AW_Giftcard_Block_Adminhtml_Form_Element_Price');
        $fieldset->addField('balance', 'price', array(
            'label'     => $this->__('Balance'),
            'title'     => $this->__('Balance'),
            'name'      => 'balance',
            'class'     => 'validate-greater-than-zero validate-number',
            'required'  => true,
            'note'      => '<div id="balance_currency"></div>'
        ));

        $fieldset->addField('expire_at', 'date', array(
            'name'      => 'expire_at',
            'label'     => $this->__('Expiration Date'),
            'title'     => $this->__('Expiration Date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));

        $form->setValues($giftcardModel->getData());

        if (null === $giftcardModel->getId()
            && $this->helper('aw_giftcard/config')->getExpireValue() > 0
        ) {
            $expireAt = Mage::app()->getLocale()->date(null, Varien_Date::DATE_INTERNAL_FORMAT, null, false);
            $expireAt->addDay($this->helper('aw_giftcard/config')->getExpireValue());
            $form->getElement('expire_at')->setValue($expireAt);
        }
        $this->setForm($form);
        return $this;
    }

    public function getCurrencyJson()
    {
        $result = array();
        $websites = Mage::getSingleton('adminhtml/system_store')->getWebsiteCollection();
        foreach ($websites as $id => $website) {
            $result[$id] = $website->getBaseCurrencyCode();
        }
        return Mage::helper('core')->jsonEncode($result);
    }
}