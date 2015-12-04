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

class AW_Giftcard_Block_Adminhtml_Giftcard_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('giftcardGrid');
        $this->setDefaultSort('entity_id', 'desc');
    }

    protected function _prepareCollection()
    {
        /** @var AW_Giftcard_Model_Mysql4_Giftcard_Collection $collection */
        $collection = Mage::getModel('aw_giftcard/giftcard')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'        => $this->__('ID'),
            'index'         => 'entity_id',
            'type'          => 'number',
            'width'         => 10
        ));

        $this->addColumn('code', array(
            'header'        => $this->__('Code'),
            'index'         => 'code',
            'type'          => 'text',
            'width'         => 250
        ));

        $this->addColumn('website', array(
            'header'    => $this->__('Website'),
            'width'     => 150,
            'index'     => 'website_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
        ));

        $this->addColumn('created_at', array(
            'header'   => $this->__('Created At'),
            'width'    => 120,
            'type'     => 'date',
            'index'    => 'created_at',
            'filter'   => 'aw_giftcard/adminhtml_widget_grid_column_filter_date'
        ));

        $this->addColumn('expire_at', array(
            'header'  => $this->__('Expiration Date'),
            'width'   => 120,
            'type'    => 'date',
            'index'   => 'expire_at',
            'default' => '--',
            'filter'   => 'aw_giftcard/adminhtml_widget_grid_column_filter_date'
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Active'),
            'width'     => 100,
            'align'     => 'center',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getModel('aw_giftcard/source_product_attribute_option_yesno')->toOptionArray(),
        ));

        $this->addColumn('state', array(
            'header'    => $this->__('Status'),
            'width'     => 100,
            'align'     => 'center',
            'index'     => 'state',
            'type'      => 'options',
            'options'   => Mage::getModel('aw_giftcard/source_giftcard_status')->toOptionArray(),
        ));

        $this->addColumn('balance', array(
            'header'        => $this->__('Balance'),
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            'type'          => 'number',
            'renderer'      => 'aw_giftcard/adminhtml_widget_grid_column_renderer_currency',
            'index'         => 'balance',
            'width'         => 200,
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('giftcard');

        $this->getMassactionBlock()->addItem('status', array(
            'label'=> $this->__('Set Active'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $this->__('To'),
                    'values' => Mage::getModel('aw_giftcard/source_product_attribute_option_yesno')->toOptionArray()
                )
            )
        ));
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getEntityId()));
    }
}