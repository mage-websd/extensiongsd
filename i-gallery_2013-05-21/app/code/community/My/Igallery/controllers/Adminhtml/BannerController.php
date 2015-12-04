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
 * Banner controller
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('igallery');

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('I-Gallery Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('igallery/adminhtml_banner'));
        $this->renderLayout();
    }

    /**
     * Create new banner page
     */
    public function addAction()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $_model = Mage::getModel('igallery/banner')->load($bannerId);

        $this->_title($_model->getId() ? $_model->getName() : $this->__('New Gallery'));

        Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('I-Gallery Manager'), Mage::helper('adminhtml')->__('I-Gallery Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('New Gallery'), Mage::helper('adminhtml')->__('New Gallery'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('igallery/adminhtml_banner_edit'))
                ->_addLeft($this->getLayout()->createBlock('igallery/adminhtml_banner_edit_tabs'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $_model = Mage::getModel('igallery/banner')->load($bannerId);

        if ($_model->getId()) {
            $this->_title($_model->getId() ? $_model->getName() : $this->__('New Gallery'));

            Mage::register('banner_data', $_model);
            Mage::register('current_banner', $_model);

            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('I-Gallery Manager'), Mage::helper('adminhtml')->__('I-Gallery Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Gallery'), Mage::helper('adminhtml')->__('Edit Gallery'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('igallery/adminhtml_banner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('igallery/adminhtml_banner_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('igallery')->__('The gallery does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    protected function updateOrSaveFriendlyUrl(My_Igallery_Model_Banner $_model)
    {
        if ($_model->getId() &&
                ($_model->getFriendlyUrl() != $_model->getOrigData('friendly_url') ||
                $_model->getStores() != $_model->getOrigData('stores')
                )) {

            foreach (Mage::getSingleton('adminhtml/system_store')->getStoreCollection() as $store) {
                $url = Mage::getModel('core/url_rewrite')->setStoreId($store->getId())->load('igallery/' . $_model->getId(), 'id_path');
                if (in_array($store->getId(), $_model->getStores())) {
                    if (!$url->getId()) {
                        $url->setIdPath('igallery/' . $_model->getId());
                        $url->setTargetPath('igallery/category/view/id/' . $_model->getId() . '/');
                        $url->setIsSystem(false);
                    }

                    $url->setRequestPath($_model->getFriendlyUrl());
                    $url->save();
                } else {
                    $url->delete();
                }
            }
        }
    }

    protected function checkFriendlyUrl(My_Igallery_Model_Banner $_model)
    {
        $validUrl = true;
        if (!$_model->getId() || ($_model->getId() && $_model->getFriendlyUrl() != $_model->getOrigData('friendly_url'))) {
            $url = Mage::getModel('core/url_rewrite')->load($_model->getFriendlyUrl(), 'request_path');
            if ($url->getId())
                $validUrl = false;
        }

        return $validUrl;
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {


            $_model = Mage::getModel('igallery/banner');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $_model->load($id);
            }
            $_model->setData($data);

            try {
                if ($id) {
                    $_model->setId($id);
                }

                if (!$this->checkFriendlyUrl($_model)) {
                    throw new Exception('Friendly Url already exists');
                }

                $_model->save();

                $this->updateOrSaveFriendlyUrl($_model);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('igallery')->__('Gallery was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $_model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('igallery')->__('Unable to find gallery to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('igallery/banner');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Gallery was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $IDList = $this->getRequest()->getParam('banner');
        if (!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select gallery(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('igallery/banner')
                                    ->setIsMassDelete(true)->load($itemId);
                    $_model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($IDList)
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
        $IDList = $this->getRequest()->getParam('banner');
        if (!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select gallery(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('igallery/banner')
                            ->setIsMassStatus(true)
                            ->load($itemId)
                            ->setIsActive($this->getRequest()->getParam('status'))
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($IDList))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function imageAction()
    {
        $result = array();
        try {
            $uploader = new My_Igallery_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('igallery/config')->getBaseMediaPath()
            );

            $result['url'] = Mage::getSingleton('igallery/config')->getMediaUrl($result['file']);
            $result['cookie'] = array(
                'name' => session_name(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function categoriesJsonAction()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $_model = Mage::getModel('igallery/banner')->load($bannerId);
        Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);

        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('igallery/adminhtml_banner_edit_tab_category')
                        ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * Add an extra title to the end or one from the end, or remove all
     *
     * Usage examples:
     * $this->_title('foo')->_title('bar');
     * => bar / foo / <default title>
     *
     * $this->_title()->_title('foo')->_title('bar');
     * => bar / foo
     *
     * $this->_title('foo')->_title(false)->_title('bar');
     * bar / <default title>
     *
     * @see self::_renderTitles()
     * @param string|false|-1|null $text
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _title($text = null, $resetIfExists = true)
    {
        if (is_string($text)) {
            $this->_titles[] = $text;
        } elseif (-1 === $text) {
            if (empty($this->_titles)) {
                $this->_removeDefaultTitle = true;
            } else {
                array_pop($this->_titles);
            }
        } elseif (empty($this->_titles) || $resetIfExists) {
            if (false === $text) {
                $this->_removeDefaultTitle = false;
                $this->_titles = array();
            } elseif (null === $text) {
                $this->_removeDefaultTitle = true;
                $this->_titles = array();
            }
        }
        return $this;
    }

}