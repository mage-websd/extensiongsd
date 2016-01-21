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


class AW_Points_Block_Adminhtml_Customer_Edit_Tabs_RewardPoints_BalanceUpdate extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_points');

        $fieldset = $form->addFieldset(
            'points_fieldset', array('legend' => Mage::helper('points')->__('Update Reward Points Balance'))
        );

        $fieldset->addField(
            'update_points',
            'text',
            array(
                 'label' => Mage::helper('points')->__('Update Points'),
                 'name'  => 'aw_update_points',
                 'note'  => Mage::helper('points')->__('Enter a negative number to subtract from balance')
            )
        );

        $fieldset->addField(
            'comment',
            'text',
            array(
                 'label' => Mage::helper('points')->__('Comment'),
                 'name'  => 'aw_update_points_comment',
            )
        );

        $_pointsExpirationDaysValidationClass = (version_compare(Mage::getVersion(), '1.7.0.2') >= 0) ? 'validate-not-negative-number' : 'validate-number';

        $fieldset->addField(
            'points_expiration_days',
            'text',
            array(
                'label'    => Mage::helper('points')->__('Reward points expire after, days'),
                'required' => false,
                'name'     => 'aw_points_expiration_days',
                'class'    => $_pointsExpirationDaysValidationClass,
                'value' => Mage::helper('points/config')->getPointsExpirationDays(),
                'note' =>
                    sprintf(
                        Mage::helper('points')->__('Default global value is %s. Set the value to "0" or leave the field empty to disable expiration for this transaction.'),
                        '<a href="'.
                        Mage::helper("adminhtml")->getUrl('adminhtml/system_config/edit', array('section' => 'points')).
                        '">'.
                        (
                            Mage::helper('points/config')->getPointsExpirationDays() ?
                            Mage::helper('points')->__('%s day(s)', Mage::helper('points/config')->getPointsExpirationDays()) :
                            Mage::helper('points')->__('not set')
                        ).
                        '</a>'
                    )
            )
        );

        $this->setForm($form);
        return $this;
    }
}
