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

class AW_Giftcard_Test_Model_Sales_Order_Totals_Quote extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function collect($dataProvider)
    {
        //deprected
        return $this;
        $_errors = null;
        try {
            $_quoteModel = Mage::getModel('sales/quote')
                ->setStoreId($dataProvider['store_id'])
                ->load($dataProvider['quote_id'])
            ;
            $_product = Mage::getModel('catalog/product')->load($dataProvider['product_id']);
            $_product
                ->setData('price', $dataProvider['product_price'])
                ->save()
            ;

            $giftcardModel = Mage::getModel('aw_giftcard/giftcard')
                ->load($dataProvider['giftcard_id'])
            ;
            Mage::helper('aw_giftcard/totals')->addCardToQuote($giftcardModel, $_quoteModel);
            $_quoteModel
                ->unsTotalsCollectedFlag()
                ->collectTotals()
                ->save()
            ;
        } catch (Exception $e) {
            $_errors = $e->getMessage();
        }
        $expected = $this->expected($dataProvider['test_id']);
        $this->assertEquals(
            $expected->getErrors(),
            $_errors
        );

        $this->assertEquals(
            $expected->getBaseGrandTotal(),
            $_quoteModel->getBaseGrandTotal()
        );

        $this->assertEquals(
            $expected->getGrandTotal(),
            $_quoteModel->getGrandTotal()
        );
    }
}