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

class AW_Giftcard_Model_Attribute_Backend_Product_Price extends Mage_Catalog_Model_Product_Attribute_Backend_Price
{
    public function validate($object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if ($object->getData('aw_gc_allow_open_amount')
            == AW_Giftcard_Model_Source_Product_Attribute_Option_Yesno::ENABLED_VALUE
            && $value < 0.01
        ) {
            Mage::throwException(
                Mage::helper('aw_giftcard')->__(
                    '%s should be 0.01 or greater.',
                    $this->getAttribute()->getData('frontend_label')
                )
            );
        }
        return parent::validate($object);
    }
}