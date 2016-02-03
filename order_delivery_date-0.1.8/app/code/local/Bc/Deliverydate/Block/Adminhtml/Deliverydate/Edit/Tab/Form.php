<?php

class Bc_Deliverydate_Block_Adminhtml_Deliverydate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('deliverydate_form', array('legend'=>Mage::helper('deliverydate')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('deliverydate')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('deliverydate')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('deliverydate')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('deliverydate')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('deliverydate')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('deliverydate')->__('Content'),
          'title'     => Mage::helper('deliverydate')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getDeliverydateData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDeliverydateData());
          Mage::getSingleton('adminhtml/session')->setDeliverydateData(null);
      } elseif ( Mage::registry('deliverydate_data') ) {
          $form->setValues(Mage::registry('deliverydate_data')->getData());
      }
      return parent::_prepareForm();
  }
}