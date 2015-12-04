<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    My
 * @package     My_Igallery
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * General form
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */

class My_Igallery_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('banner_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('igallery_form', array('legend'=>Mage::helper('igallery')->__('General Information')));
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('igallery')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
            'value'     => $_model->getName()
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('igallery')->__('Sort Order'),
            'required'  => false,
            'name'      => 'sort_order',
            'value'     => $_model->getSortOrder()
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('igallery')->__('Is Active'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => $_model->getIsActive()
        ));

        $fieldset->addField('friendly_url', 'text', array(
            'label'     => Mage::helper('igallery')->__('SEO Friendly Url'),
            'name'      => 'friendly_url',
            'value'     => $_model->getFriendlyUrl(),
            'after_element_html' => '<small>This is the url path for the gallery<br>e.g igallery/photo-gallery-1</small>'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                'label'     => Mage::helper('igallery')->__('Visible In'),
                'required'  => false,
                'name'      => 'stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false),
                'value'     => $_model->getStoreId()
            ));
        } else {
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
        }

        $layoutFieldset = $form->addFieldset('layout_fieldset', array(
            'legend' => Mage::helper('igallery')->__('Page Layout')
        ));
        $layoutFieldset->addField('page_layout', 'select', array(
            'name'     => 'page_layout',
            'label'    => Mage::helper('igallery')->__('Page Layout'),
            'values'   => Mage::getSingleton('page/source_layout')->toOptionArray(true),
            'value'    => $_model->getPageLayout()
        ));
        $layoutFieldset->addField('column_count', 'text', array(
            'label'     => Mage::helper('igallery')->__('Column Count For Image Grid'),
            'name'      => 'column_count',
            'value'     => $_model->getColumnCount() ? $_model->getColumnCount() : 4
        ));
        $layoutFieldset->addField('description', 'textarea', array(
            'name'      => 'description',
            'label'     => Mage::helper('cms')->__('Text/HTML Content'),
            'title'     => Mage::helper('cms')->__('Text/HTML Content'),
            'required'  => false,
            'value'     => $_model->getDescription()
        ));

        if( Mage::getSingleton('adminhtml/session')->getBannerData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        }
        
        return parent::_prepareForm();
    }
}
