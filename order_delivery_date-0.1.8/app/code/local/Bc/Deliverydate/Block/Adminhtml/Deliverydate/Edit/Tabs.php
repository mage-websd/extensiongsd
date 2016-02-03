<?php

class Bc_Deliverydate_Block_Adminhtml_Deliverydate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('deliverydate_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('deliverydate')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('deliverydate')->__('Item Information'),
          'title'     => Mage::helper('deliverydate')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('deliverydate/adminhtml_deliverydate_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}