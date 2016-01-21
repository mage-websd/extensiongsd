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


class AW_Points_Model_Source_Pointsfororder
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => AW_Points_Helper_Config::FIRST_ORDER_ONLY,
                'label' => Mage::helper('points')->__('First order')
            ),
            array(
                'value' => AW_Points_Helper_Config::EACH_ORDER,
                'label' => Mage::helper('points')->__('Each order')
            )
        );
    }
}