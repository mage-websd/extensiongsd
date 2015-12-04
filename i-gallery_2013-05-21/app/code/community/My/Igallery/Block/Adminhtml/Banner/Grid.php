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
 * Banner grid
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('igallery/banner')->getCollection();
        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('banner_id', array(
                'header'    => Mage::helper('igallery')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'banner_id',
        ));

        $this->addColumn('name', array(
                'header'    => Mage::helper('igallery')->__('Title'),
                'align'     =>'left',
                'index'     => 'name',
        ));

        $this->addColumn('sort_order', array(
                'header'    => Mage::helper('igallery')->__('Sort Order'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'sort_order',
        ));

        $this->addColumn('is_active', array(
                'header'    => Mage::helper('igallery')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'is_active',
                'type'      => 'options',
                'options'   => array(
                        1 => Mage::helper('igallery')->__('Enabled'),
                        0 => Mage::helper('igallery')->__('Disabled'),
                //3 => Mage::helper('igallery')->__('Hidden'),
                ),
        ));

        $this->addColumn('action',
                array(
                'header'    =>  Mage::helper('igallery')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                        array(
                                'caption'   => Mage::helper('igallery')->__('Edit'),
                                'url'       => array('base'=> '*/*/edit'),
                                'field'     => 'id'
                        ),
                        array(
                                'caption'   => Mage::helper('igallery')->__('Delete'),
                                'url'       => array('base'=> '*/*/delete'),
                                'field'     => 'id'
                        )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('banner');

        $this->getMassactionBlock()->addItem('delete', array(
                'label'    => Mage::helper('igallery')->__('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('igallery')->__('Are you sure?')
        ));

        $statuses = array(
                1 => Mage::helper('igallery')->__('Enabled'),
                2 => Mage::helper('igallery')->__('Disabled'));
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
                'label'=> Mage::helper('igallery')->__('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                        'visibility' => array(
                                'name' => 'status',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => Mage::helper('igallery')->__('Status'),
                                'values' => $statuses
                        )
                )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}