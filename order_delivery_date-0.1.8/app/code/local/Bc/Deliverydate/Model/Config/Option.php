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
    * @category    Mage
    * @package     Mage_Customer
    * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
    * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
    */

    /**
    * Customer sharing config model
    *
    * @category   Mage
    * @package    Mage_Customer
    * @author      Magento Core Team <core@magentocommerce.com>
    */
    class Bc_Deliverydate_Model_Config_Option extends Mage_Core_Model_Config_Data
    {
        const TYPE_SHIPPING_PAGE = 1;
        const TYPE_REVIEW_PAGE   = 2;

        /**
        * Get possible sharing configuration options
        *
        * @return array
        */
        public function toOptionArray()
        {
            return array(
                self::TYPE_SHIPPING_PAGE  => Mage::helper('deliverydate')->__('Shipping Method'),
                self::TYPE_REVIEW_PAGE => Mage::helper('deliverydate')->__('Review Page'),
            );
        }

    }
