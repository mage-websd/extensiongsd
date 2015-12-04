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

class AW_Giftcard_Test_Model_Giftcard extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function isValidForRedeem($dataProvider)
    {
        try {
            $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->load($dataProvider['giftcard_id']);
            $_result = $giftcardModel->isValidForRedeem($dataProvider['store_id']);
        } catch (Exception $e) {
            $_result = $e->getMessage();
        }
        $expected = $this->expected($dataProvider['test_id']);
        $this->assertEquals(
            $expected->getResult(),
            $_result
        );
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function save($dataProvider)
    {
        try {
            $giftcardModel = Mage::getModel('aw_giftcard/giftcard');
            $giftcardModel
                ->addData($dataProvider)
                ->save()
            ;
            $_result = $giftcardModel->getCode();
        } catch (Exception $e) {
            $_result = $e->getMessage();
        }

        $expected = $this->expected($dataProvider['test_id']);
        $this->assertRegExp(
            $expected->getRegexp(),
            $_result
        );
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function loadByCode($dataProvider)
    {
        try {
            $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->loadByCode($dataProvider['code']);
            $_result = $giftcardModel->getId();
        } catch (Exception $e) {
            $_result = $e->getMessage();
        }

        $expected = $this->expected($dataProvider['test_id']);
        $this->assertEquals(
            $expected->getResult(),
            $_result
        );
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function delete($dataProvider)
    {
        $_errors = null;
        try {
            $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->load($dataProvider['giftcard_id']);
            $giftcardModel->delete();
        } catch (Exception $e) {
            $_errors = $e->getMessage();
        }

        $_giftcard = Mage::getModel('aw_giftcard/giftcard')->load($dataProvider['giftcard_id']);

        $_historyCollection = Mage::getModel('aw_giftcard/history')->getCollection();
        $_historyCollection->getSelect()->where('giftcard_id = ?', $dataProvider['giftcard_id']);

        $_quoteCollection = Mage::getModel('aw_giftcard/quote_giftcard')->getCollection();
        $_quoteCollection->getSelect()->where('giftcard_id = ?', $dataProvider['giftcard_id']);

        $_invoiceCollection = Mage::getModel('aw_giftcard/order_invoice_giftcard')->getCollection();
        $_invoiceCollection->getSelect()->where('giftcard_id = ?', $dataProvider['giftcard_id']);

        $_creditmemoCollection = Mage::getModel('aw_giftcard/order_creditmemo_giftcard')->getCollection();
        $_creditmemoCollection->getSelect()->where('giftcard_id = ?', $dataProvider['giftcard_id']);

        $expected = $this->expected($dataProvider['test_id']);

        $this->assertEquals(
            $expected->getGiftcardId(),
            $_giftcard->getId()
        );

        $this->assertEquals(
            $expected->getHistory(),
            $_historyCollection->getSize()
        );

        $this->assertEquals(
            $expected->getInvoice(),
            $_invoiceCollection->getSize()
        );

        $this->assertEquals(
            $expected->getCreditmemo(),
            $_creditmemoCollection->getSize()
        );

        $this->assertEquals(
            $expected->getErrors(),
            $_errors
        );
    }
}