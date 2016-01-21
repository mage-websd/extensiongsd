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


class AW_Points_Model_Rule extends Mage_Rule_Model_Rule
{
    public function _construct()
    {
        Mage_Rule_Model_Rule::_construct();
        $this->_init('points/rule');
        $this->setIdFieldName('rule_id');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('points/rule_condition_combine');
    }

    public function getResourceCollection()
    {
        return Mage::getResourceModel('points/rule_collection');
    }

    public function checkRule($quote)
    {
        if (!$this->getIsActive()) {
            return false;
        }
        $this->afterLoad();
        return $this->validate($quote);
    }

    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->loadArray($arr['conditions'][1]);
        }
        return $this;
    }

    public function toString($format = '')
    {
        $helper = Mage::helper('points');
        $str = $helper->__('Name: %s', $this->getName()) . "\n"
            . $helper->__('Start at: %s', $this->getStartAt()) . "\n"
            . $helper->__('Expire at: %s', $this->getExpireAt()) . "\n"
            . $helper->__('Description: %s', $this->getDescription()) . "\n\n"
            . $this->getConditions()->toStringRecursive() . "\n\n";
        return $str;
    }
}