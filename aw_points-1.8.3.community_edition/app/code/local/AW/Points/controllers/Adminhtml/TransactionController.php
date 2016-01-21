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


class AW_Points_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    protected function displayTitle()
    {
        if (!Mage::helper('points')->magentoLess14()) {
            $this->_title($this->__('Rewards'))->_title($this->__('Transactions'));
        }
        return $this;
    }

    public function indexAction()
    {
        $this
            ->displayTitle()
            ->loadLayout()
            ->_setActiveMenu('promo')
            ->_addContent($this->getLayout()->createBlock('points/adminhtml_transaction'))
            ->renderLayout();
    }

    public function editAction()
    {
        $transaction = Mage::getModel('points/transaction')->load($this->getRequest()->getParam('id'));

        Mage::register('points_current_transaction', $transaction);
        $breadcrumbTitle = $breadcrumbLabel = Mage::helper('points')->__('View Transaction');

        $this
            ->displayTitle()
            ->loadLayout()
            ->_setActiveMenu('promo')
            ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
            ->_addContent($this->getLayout()->createBlock('points/adminhtml_transaction_edit'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this
            ->displayTitle()
            ->loadLayout()
            ->_setActiveMenu('promo')
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($this->getRequest()->getParam('transaction_id')) {
            $transaction = Mage::getModel('points/transaction')->load($this->getRequest()->getParam('transaction_id'));

            if ($this->getRequest()->getParam('expiration_date', false) !== false) {
                if (trim($this->getRequest()->getParam('expiration_date')) === '') {
                    $transaction->setExpirationDate(null);
                }
                else {
                    $expirationDate = Mage::app()->getLocale()->date(
                        $this->getRequest()->getParam('expiration_date'),
                        Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                        null,
                        false
                );
                    $currentDate = new Zend_Date();

                    if ($expirationDate->compare($currentDate, Zend_Date::DATES) < 0) {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('points')->__('The Expiration Date is in the past')
                        );
                        return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('transaction_id')));
                    }
                    else {
                        $expirationDate->setTime($currentDate, Zend_Date::TIMES);
                        $transaction->setExpirationDate($expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
                    }
                }
            }

            try {
                $transaction->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('points')->__('Transaction saved'));
            } catch (Exception $ex) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('points')->__('Error while saving transaction'));
            }
            return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('transaction_id')));
        }
        else {
            $post = $this->getRequest()->getPost();
            if (empty($post['comment']) || empty($post['balance_change']) || empty($post['selected_customers'])) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('points')->__(
                            'Comments and Balance Change cannot be empty, at least one customer must be selected'
                        )
                    );
                return $this->_redirect('*/*/new');
            }
            try {
                $customersIds = explode(',', $post['selected_customers']);
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    Mage::getModel('points/api')->addTransaction(
                        $post['balance_change'],
                        'added_by_admin',
                        $customer,
                        null,
                        array('comment' => $post['comment']),
                        array(
                            'store_id' => $this->_getStoreId($customer),
                            'points_expiration_days' => $post['points_expiration_days']
                        )
                    );
                }
            } catch (Exception $ex) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('points')->__('Error while saving transaction'));
                return $this->_redirect('*/*/new');
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('points')->__('Transaction(s) successfully created')
            );
            return $this->_redirect('*/*/index');
        }
    }

    public function customersGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('points/adminhtml_customer_grid')->toHtml());
    }

    public function massSubscribeAction()
    {
        $arrayOfCustomersId = $this->getRequest()->getParam('selected_customers_form');
        if (isset($arrayOfCustomersId)) {
            $countOfRecords = 0;
            foreach ($arrayOfCustomersId as $customerID) {
                if ($customerID == 0) {
                    continue;
                }

                $summary = Mage::getModel('points/summary')->loadByCustomerID($customerID);

                if (
                    $summary->getData('balance_update_notification') == 1
                    && $summary->getData('points_expiration_notification') == 1
                ) {
                    $countOfRecords++;
                    continue;
                }

                $summary
                    ->setBalanceUpdateNotification(1)
                    ->setPointsExpirationNotification(1)
                    ->setUpdateDate(true)
                    ->save();

                $countOfRecords++;
            }
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('points')->__('Total of %s record(s) were updated.', $countOfRecords));
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('points')->__('Error while subscribing for newsletter'));
        }
        $this->_redirectReferer();
    }

    public function resetTransactionsAction()
    {
        try {

            $transactionTableName = Mage::getSingleton('core/resource')->getTableName('points/transaction');
            $summaryTableName = Mage::getSingleton('core/resource')->getTableName('points/summary');
            $couponTable = Mage::getSingleton('core/resource')->getTableName('points/coupon');
            $couponTransactionTable = Mage::getSingleton('core/resource')->getTableName('points/coupon_transaction');

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $write->truncate($transactionTableName);
            $write->truncate($couponTransactionTable);
            $write->exec("UPDATE `$couponTable` SET `activation_cnt`=0 WHERE 1");
            $write->exec("DELETE FROM `{$summaryTableName}` WHERE 1");

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('points')->__('Transaction(s) successfully reseted'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('points')->__('Error while resetting transaction(s)'));
            Mage::helper('awcore/logger')->log(
                $this, Mage::helper('points')->__('Error while resetting transaction(s)'),
                AW_Core_Model_Logger::LOG_SEVERITY_ERROR, $e->getMessage(), $e->getLine()
            );
        }
        $this->_redirectReferer();
    }

    protected function _getStoreId($customer)
    {
        try {
            $store = $customer->getStore();
            if ($store instanceof Varien_Object) {
                return $store->getId();
            }
        } catch (Mage_Core_Model_Store_Exception $e) {
            Mage::helper('awcore/logger')->log(
                $this,
                sprintf('Store related to customer #%d not found', $customer->getId()),
                AW_Core_Model_Logger::LOG_SEVERITY_NOTICE
            );
        } catch (Exception $e) {

        }
        return null;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/points/transactions');
    }
}
