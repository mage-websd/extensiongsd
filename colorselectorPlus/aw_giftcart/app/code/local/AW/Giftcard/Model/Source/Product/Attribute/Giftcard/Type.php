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

class AW_Giftcard_Model_Source_Product_Attribute_Giftcard_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const VIRTUAL_VALUE  = 1;
    const PHYSICAL_VALUE = 2;
    const COMBINED_VALUE = 3;

    const VIRTUAL_LABEL  = 'Virtual';
    const PHYSICAL_LABEL = 'Physical';
    const COMBINED_LABEL = 'Combined';

    public function getAllOptions()
    {
        $result = array();
        foreach ($this->_getOptions() as $k => $v) {
            $result[] = array(
                'value' => $k,
                'label' => $v,
            );
        }
        return $result;
    }

    public function getOptionText($value)
    {
        $options = $this->_getOptions();
        if (isset($options[$value])) {
            return $options[$value];
        }
        return null;
    }

    protected function _getOptions()
    {
        return array(
            self::VIRTUAL_VALUE   => Mage::helper('aw_giftcard')->__(self::VIRTUAL_LABEL),
            self::PHYSICAL_VALUE  => Mage::helper('aw_giftcard')->__(self::PHYSICAL_LABEL),
            self::COMBINED_VALUE  => Mage::helper('aw_giftcard')->__(self::COMBINED_LABEL),
        );
    }

    public function toOptionArray($typeValue = null)
    {
        $options = $this->_getOptions();
        if (null !== $typeValue) {
            if (array_key_exists($typeValue, $options)) {
                $options = $options[$typeValue];
            }
        }
        return $options;
    }

    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => true,
            'default'   => null,
            'extra'     => null
        );

        if (
            !method_exists(Mage::helper('core'), 'useDbCompatibleMode')
            || Mage::helper('core')->useDbCompatibleMode()
        ) {
            $column['type']     = 'tinyint';
            $column['is_null']  = true;
        }
        else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_SMALLINT;
            $column['nullable'] = true;
        }

        return array($attributeCode => $column);
    }

    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceSingleton('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }

}
