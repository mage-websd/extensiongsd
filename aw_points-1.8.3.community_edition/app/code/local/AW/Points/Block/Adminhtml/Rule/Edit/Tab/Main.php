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
 * @package    AW_Points
 * @version    1.8.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Adminhtml_Rule_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('points_rule_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $helper = Mage::helper('points');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('General Information')));

        if ($model->getId()) {
            $fieldset->addField(
                'rule_id',
                'hidden',
                array(
                     'name' => 'rule_id',
                )
            );
        }

        $fieldset->addField(
            'name',
            'text',
            array(
                 'name'     => 'name',
                 'label'    => $helper->__('Rule Title'),
                 'title'    => $helper->__('Rule Title'),
                 'required' => true,
            )
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                 'name'  => 'description',
                 'label' => Mage::helper('salesrule')->__('Description'),
                 'title' => Mage::helper('salesrule')->__('Description'),
                 'style' => 'width: 98%; height: 100px;',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'website_ids',
                'multiselect',
                array(
                     'name'     => 'website_ids[]',
                     'label'    => Mage::helper('catalogrule')->__('Websites'),
                     'title'    => Mage::helper('catalogrule')->__('Websites'),
                     'required' => true,
                     'values'   => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
                )
            );
        } else {
            $fieldset->addField(
                'website_ids',
                'hidden',
                array(
                     'name'  => 'website_ids[]',
                     'value' => Mage::app()->getStore(true)->getWebsiteId()
                )
            );
            $model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }

        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();

        foreach ($customerGroups as $key => $group) {
            if ($group['value'] == 0) {
                unset($customerGroups[$key]);
            }
        }

        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            array(
                 'name'     => 'customer_group_ids[]',
                 'label'    => Mage::helper('salesrule')->__('Customer Groups'),
                 'title'    => Mage::helper('salesrule')->__('Customer Groups'),
                 'required' => true,
                 'values'   => $customerGroups,
            )
        );

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            array(
                 'name'         => 'from_date',
                 'label'        => Mage::helper('salesrule')->__('From Date'),
                 'title'        => Mage::helper('salesrule')->__('From Date'),
                 'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                 'format'       => $dateFormatIso,
            )
        );
        $fieldset->addField(
            'to_date',
            'date',
            array(
                 'name'         => 'to_date',
                 'label'        => Mage::helper('salesrule')->__('To Date'),
                 'title'        => Mage::helper('salesrule')->__('To Date'),
                 'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                 'format'       => $dateFormatIso,
            )
        );

        $fieldset->addField(
            'is_active',
            'select',
            array(
                 'label'   => $helper->__('Status'),
                 'title'   => $helper->__('Status'),
                 'name'    => 'is_active',
                 'options' => array(
                     '1' => $helper->__('Active'),
                     '0' => $helper->__('Inactive'),
                 ),
            )
        );

        $fieldset->addField(
            'stop_rules',
            'select',
            array(
                 'label'   => $helper->__('Stop further rules processing'),
                 'title'   => $helper->__('Stop further rules processing'),
                 'name'    => 'stop_rules',
                 'options' => array(
                     '1' => $helper->__('Yes'),
                     '0' => $helper->__('No'),
                 ),
            )
        );

        $afterPriorityHtml = '<p class="note"><span>'
            . $this->__("Priority is arranged in the ascending order, 0 the highest")
            . '</span></p>'
        ;

        $fieldset->addField(
            'priority',
            'text',
            array(
                 'name'               => 'priority',
                 'label'              => $helper->__('Priority'),
                 'title'              => $helper->__('Priority'),
                 'class'              => "validate-zero-or-greater",
                 'after_element_html' => $afterPriorityHtml,
            )
        );

        $fieldset->addField(
            'static_blocks_ids',
            'hidden',
            array(
                 'name' => 'static_blocks_ids',
            )
        );

        $fieldset->addField(
            'save_as_flag',
            'hidden',
            array(
                 'name'  => '_save_as_flag',
                 'value' => 0,
            )
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}