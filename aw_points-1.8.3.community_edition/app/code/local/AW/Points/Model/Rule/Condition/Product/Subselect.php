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


class AW_Points_Model_Rule_Condition_Product_Subselect extends AW_Points_Model_Rule_Condition_Product_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('points/rule_condition_product_subselect')
            ->setValue(null);
    }

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' . $this->getAttribute() . '</attribute>'
            . '<operator>' . $this->getOperator() . '</operator>'
            . parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            array(
                 'qty'       => Mage::helper('points')->__('total quantity'),
                 'row_total' => Mage::helper('points')->__('total amount'),
            )
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        $helper = Mage::helper('points');
        $this->setOperatorOption(
            array(
                 '=='  => $helper->__('is'),
                 '!='  => $helper->__('is not'),
                 '>='  => $helper->__('equals or greater than'),
                 '<='  => $helper->__('equals or less than'),
                 '>'   => $helper->__('greater than'),
                 '<'   => $helper->__('less than'),
                 '()'  => $helper->__('is one of'),
                 '!()' => $helper->__('is not one of'),
            )
        );
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('points')->__(
                "If %s %s %s for a subselection of items in cart matching %s of these conditions:",
                $this->getAttributeElement()->getHtml(), $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     *
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }
        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getAllItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }
        return $this->validateAttribute($total);
    }
}