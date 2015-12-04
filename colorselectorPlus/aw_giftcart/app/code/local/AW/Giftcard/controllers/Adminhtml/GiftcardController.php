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

class AW_Giftcard_Adminhtml_GiftcardController extends Mage_Adminhtml_Controller_Action
{
    protected function _initGiftCard()
    {
        $giftcardModel = Mage::getModel('aw_giftcard/giftcard');
        $giftcardId  = (int) $this->getRequest()->getParam('id', false);
        if ($giftcardId) {
            try {
                $giftcardModel->load($giftcardId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        if (null !== Mage::getSingleton('adminhtml/session')->getFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getFormData())
        ) {
            $giftcardModel->addData(Mage::getSingleton('adminhtml/session')->getFormData());
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }
        Mage::register('current_giftcard', $giftcardModel);
        return $giftcardModel;
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/giftcard');

        $this
            ->_title($this->__('Customers'))
            ->_title($this->__('Gift Cards'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Manage Gift Cards'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $giftcard = $this->_initGiftCard();
        $this->_initAction();
        $this->_title($this->__('Manage Gift Cards'));

        if ($giftcard->getId()) {
            $breadcrumbTitle = $breadcrumbLabel = $this->__('Edit Gift Card');
        } else {
            $breadcrumbTitle = $breadcrumbLabel = $this->__('New Gift Card');
        }

        $this
            ->_title($breadcrumbTitle)
            ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
            ->renderLayout()
        ;
    }

    public function saveAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            if ($this->getRequest()->getPost('expire_at', null)) {
                $formData = $this->_filterDates($formData, array('expire_at'));
            } else {
                $formData['expire_at'] = null;
            }

            $giftcardModel = $this->_initGiftCard();
            try {
                $giftcardModel
                    ->addData($formData)
                    ->save()
                ;
                Mage::getSingleton('adminhtml/session')->setFormData(null);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit',
                        array(
                            'id'  => $giftcardModel->getId(),
                            'tab' => $this->getRequest()->getParam('tab', null)
                        )
                    );
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id'  => $giftcardModel->getId(),
                        'tab' => $this->getRequest()->getParam('tab', null)
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function gridHistoryAction()
    {
        $giftcardModel = $this->_initGiftCard();
        if (null === $giftcardModel->getId()) {
            return;
        }
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('aw_giftcard/adminhtml_giftcard_edit_tab_history')->toHtml()
        );
    }

    public function exportCsvAction()
    {
        $fileName = 'aw_giftcard.csv';
        $content = $this->getLayout()->createBlock('aw_giftcard/adminhtml_giftcard_grid')
            ->getCsvFile()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'aw_giftcard.xml';
        $content = $this->getLayout()->createBlock('aw_giftcard/adminhtml_giftcard_grid')
            ->getExcelFile()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massStatusAction()
    {
        $giftcardIds = $this->getRequest()->getParam('giftcard', null);
        $status = $this->getRequest()->getParam('status', null);
        try {
            if (!is_array($giftcardIds)) {
                throw new Mage_Core_Exception($this->__('Invalid giftcard ids'));
            }

            if (null === $status) {
                throw new Mage_Core_Exception($this->__('Invalid status value'));
            }
            foreach ($giftcardIds as $id) {
                Mage::getSingleton('aw_giftcard/giftcard')
                    ->load($id)
                    ->setStatus($status)
                    ->save()
                ;
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('%d giftcard(s) have been successfully updated', count($giftcardIds))
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $giftcardIds = $this->getRequest()->getParam('giftcard', null);
        try {
            if (!is_array($giftcardIds)) {
                throw new Mage_Core_Exception($this->__('Invalid giftcard ids'));
            }

            foreach ($giftcardIds as $id) {
                Mage::getSingleton('aw_giftcard/giftcard')
                    ->load($id)
                    ->delete()
                ;
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('%d giftcard(s) have been successfully deleted', count($giftcardIds))
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function deleteAction()
    {
        $giftcardModel = $this->_initGiftCard();
        try {
            $giftcardModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Giftcard have been successfully deleted')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }
}