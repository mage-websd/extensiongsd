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


class AW_Points_Block_Adminhtml_Transaction_Add_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('points');

        $form = new Varien_Data_Form(
            array(
                 'id'      => 'transaction_add_form',
                 'action'  => $this->getUrl('*/*/save'),
                 'method'  => 'post',
                 'enctype' => 'multipart/form-data',
            )
        );

        $fieldset = $form->addFieldset('main_group', array('legend' => Mage::helper('points')->__('Fields')));

        $fieldset->addField(
            'comment',
            'text',
            array(
                 'label'    => $helper->__('Comment'),
                 'required' => true,
                 'name'     => 'comment',
            )
        );

        $fieldset->addField(
            'balance_change',
            'text',
            array(
                 'label'    => $helper->__('Points Balance Change'),
                 'required' => true,
                 'name'     => 'balance_change',
                 'class'    => 'validate-number',
            )
        );

        $_pointsExpirationDaysValidationClass = (version_compare(Mage::getVersion(), '1.7.0.2') >= 0) ? 'validate-not-negative-number' : 'validate-number';

        $fieldset->addField(
            'points_expiration_days',
            'text',
            array(
                'label'    => $helper->__('Reward points expire after, days'),
                'required' => false,
                'name'     => 'points_expiration_days',
                'class'    => $_pointsExpirationDaysValidationClass,
                'value' => Mage::helper('points/config')->getPointsExpirationDays(),
                'note' =>
                    sprintf(
                        $helper->__('Default global value is %s. Set the value to "0" or leave the field empty to disable expiration for this transaction.'),
                        '<a href="'.
                        Mage::helper("adminhtml")->getUrl('adminhtml/system_config/edit', array('section' => 'points')).
                        '">'.
                        (
                            Mage::helper('points/config')->getPointsExpirationDays() ?
                            $helper->__('%s day(s)', Mage::helper('points/config')->getPointsExpirationDays()) :
                            $helper->__('not set')
                        ).
                        '</a>'
                    )
            )
        );

        $fieldset->addField(
            'selected_customers',
            'hidden',
            array(
                 'name' => 'selected_customers',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
