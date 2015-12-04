<?php
/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013 Indianic
 */

/**
 * FAQ for Magento
 *
 * @category   Inic
 * @package    Inic_Faq
 * @author     Inic
 */
class Inic_Faq_Block_Adminhtml_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor of Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('faq_grid');
        $this->setUseAjax(false);
        $this->setDefaultSort('creation_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Preparation of the data that is displayed by the grid.
     *
     * @return Inic_Faq_Block_Admin_Grid Self
     */
    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = Mage::getResourceModel('faq/faq_collection');
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    /**
     * Preparation of the requested columns of the grid
     *
     * @return Inic_Faq_Block_Admin_Grid Self
     */
    protected function _prepareColumns()
    {
        $this->addColumn('faq_id', array (
                'header' => Mage::helper('faq')->__('FAQ #'), 
                'width' => '80px', 
                'type' => 'text',
                'index' => 'faq_id',
                'filter_index' => 'main_table.faq_id',
                ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', 
                   	array (
                            'header' => Mage::helper('faq')->__('Store view'), 
                            'index' => 'store_id', 
                            'type' => 'store', 
                            'store_all' => true, 
                            'store_view' => true, 
                            'sortable' => false,
                            //'display_deleted' => true,
                            'filter_condition_callback' => array($this,'_filterStoreCondition'),
             		)
             );
        }
        $this->addColumn('category_name', 
        	array (
        		'header' => Mage::helper('faq')->__('Category'), 
                'index' => 'category_name', 
                'sortable' => false,
                'filter' => false,
                'renderer' => 'faq/adminhtml_widget_grid_column_renderer_category',
                //'nl2br'         => true,
                //'filter_condition_callback' => array($this,'_filterCategoryCondition'),
            )
        );
        
        
        
        $this->addColumn('question', array (
                'header' => Mage::helper('faq')->__('Question'), 
                'index' => 'question' 
                ));
        
        $this->addColumn('is_active', 
                array (
                        'header' => Mage::helper('faq')->__('Active'), 
                        'index' => 'is_active', 
                        'type' => 'options',
                        'width' => '70px',
                        'options' => array (
                                0 => Mage::helper('faq')->__('No'), 
                                1 => Mage::helper('faq')->__('Yes') ) ));
        
        $this->addColumn('action', 
                array (
                        'header' => Mage::helper('faq')->__('Action'), 
                        'width' => '50px', 
                        'type' => 'action', 
                        'getter' => 'getId', 
                        'actions' => array (
                                array (
                                        'caption' => Mage::helper('faq')->__('Edit'), 
                                        'url' => array (
                                                'base' => 'adminhtml/faq/edit' ), 
                                        'field' => 'faq_id' ) ), 
                        'filter' => false, 
                        'sortable' => false, 
                        'index' => 'stores', 
                        'is_system' => true ));
        
        return parent::_prepareColumns();
    }
    
    /**
     * Helper function to do after load modifications
     *
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Helper function to add store filter condition
     *
     * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection Data collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column Column information to be filtered
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }
    
    /**
     * Helper function to reveive on row click url
     *
     * @param Inic_Faq_Model_Faq $row Current rows dataset
     * @return string URL for current row's onclick event
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/faq/edit', array (
                'faq_id' => $row->getFaqId() ));
    }

    /**
     * Helper function to receive grid functionality urls for current grid
     *
     * @return string Requested URL
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/faq/index', array (
                '_current' => true ));
    }
    
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('faq_id');
        $this->getMassactionBlock()->setFormFieldName('faq_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faq')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faq')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faq/status')->getOptionArray();
        
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('faq')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'is_active',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('faq')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }
    
}
