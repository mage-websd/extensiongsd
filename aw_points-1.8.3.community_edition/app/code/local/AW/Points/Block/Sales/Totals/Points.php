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


class AW_Points_Block_Sales_Totals_Points extends Mage_Core_Block_Template
{
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals()
    {
        if ($this->getOrder()->getMoneyForPoints()) {
            $source = $this->getSource();
            $this->getParentBlock()->addTotal(
                new Varien_Object(
                    array(
                         'code'   => 'points',
                         'strong' => false,
                         'label'  => Mage::helper('points/config')->getPointUnitName(),
                         'value'  => $source->getMoneyForPoints(),
                    )
                ),
                'subtotal'
            );
        }
        return $this;
    }
}