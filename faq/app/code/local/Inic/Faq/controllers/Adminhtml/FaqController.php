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
class Inic_Faq_Adminhtml_FaqController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Initialization of current view - add's breadcrumps and the current menu status
	 * 
	 * @return Inic_Faq_AdminController
	 */
	protected function _initAction()
	{
		$this->_usedModuleName = 'faq';
		
		$this->loadLayout()
				->_setActiveMenu('inic/faq')
				->_addBreadcrumb($this->__('Inic'), $this->__('Inic'))
				->_addBreadcrumb($this->__('FAQ'), $this->__('FAQ'));
				
		return $this;
	}

	/**
	 * Displays the FAQ overview grid.
	 * 
	 */
	public function indexAction()
	{
		$this->_initAction()
    			->_addContent($this->getLayout()->createBlock('faq/adminhtml_item'))
    			->renderLayout();
	}
	
	/**
	 * Displays the new FAQ item form
	 */
	public function newAction()
	{
		$this->_forward('edit');
	}
	
	/**
	 * Displays the new FAQ item form or the edit FAQ item form.
	 */
	public function editAction()
	{
		$id = $this->getRequest()->getParam('faq_id');
		$model = Mage::getModel('faq/faq');
		
		// if current id given -> try to load and edit current FAQ item
		if ($id) {
			$model->load($id);
			if (!$model->getId()) {
				Mage::getSingleton('adminhtml/session')->addError(
					Mage::helper('faq')->__('This FAQ item no longer exists')
				);
				$this->_redirect('*/*/');
				return;
			}
		}
		
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
		
		Mage::register('faq', $model);
		
		$this->_initAction()
				->_addBreadcrumb(
					$id
						? Mage::helper('faq')->__('Edit FAQ Item')
						: Mage::helper('faq')->__('New FAQ Item'),
					$id
						? Mage::helper('faq')->__('Edit FAQ Item')
						: Mage::helper('faq')->__('New FAQ Item')
				)
				->_addContent(
						$this->getLayout()
								->createBlock('faq/adminhtml_item_edit')
								->setData('action', $this->getUrl('adminhtml/faq/save'))
				)
				->_addLeft($this->getLayout()->createBlock('faq/adminhtml_item_edit_tabs'));
		
		$this->renderLayout();
	}

	/**
	 * Action that does the actual saving process and redirects back to overview
	 */
	public function saveAction()
	{
		// check if data sent
		if ($data = $this->getRequest()->getPost()) {
			// init model and set data
			$model = Mage::getModel('faq/faq');
			$model->setData($data);
			
			// try to save it
			try {
				// save the data
				$model->save();
				
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('faq')->__('FAQ Item was successfully saved')
				);
				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array (
							'faq_id' => $model->getId() ));
					return;
				}
				// go to grid
				$this->_redirect('*/*/');
				return;
			
			}
			catch (Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				// save data in session
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				// redirect to edit form
				$this->_redirect('*/*/edit', array (
						'faq_id' => $this->getRequest()->getParam('faq_id') ));
				return;
			}
		}
		$this->_redirect('*/*/');
	}

	
	/**
	 * Simple access control
	 *
	 * @return boolean True if user is allowed to edit FAQ
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('admin/inic/faq');
	}

	/**
	 * Action that does the actual saving process and redirects back to overview
	 */
	public function deleteAction()
	{
		// check if we know what should be deleted
		if ($id = $this->getRequest()->getParam('faq_id')) {
			try {
				
				// init model and delete
				$model = Mage::getModel('faq/faq');
				$model->load($id);
				$model->delete();
				
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('faq')->__('FAQ Entry was successfully deleted'));
				
				// go to grid
				$this->_redirect('*/*/');
				return;
			
			}
			catch (Exception $e) {
				
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				
				// go back to edit form
				$this->_redirect('*/*/edit', array (
						'faq_id' => $id ));
				return;
			}
		}
		
		// display error message
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('faq')->__('Unable to find a FAQ entry to delete'));
		
		// go to grid
		$this->_redirect('*/*/');
	}
	
	 public function massDeleteAction() {
        $faqIds = $this->getRequest()->getParam('faq_id');
        if(!is_array($faqIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                
                foreach ($faqIds as $faqId) {
                    $faqItem = Mage::getModel('faq/faq')->load($faqId);
                    $faqItem->delete();
                }
               
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($faqIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massStatusAction()
    {
        $faqIds = $this->getRequest()->getParam('faq_id');
        if(!is_array($faqIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($faqIds as $faqId) {
                    $faqItem = Mage::getModel('faq/faq')
                        ->load($faqId)
                        ->setIsActive($this->getRequest()->getParam('is_active'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($faqIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
}
