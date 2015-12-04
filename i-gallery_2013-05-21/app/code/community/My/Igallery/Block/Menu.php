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
 * @category    My
 * @package     My_Igallery
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Menu Block
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Block_Menu extends Mage_Core_Block_Template {
    protected $_isActive = 1;
    protected $_collection;

    protected function _getCollection($position = null) {
        if ($this->_collection) {
            return $this->_collection;
        }

        $storeId = Mage::app()->getStore()->getId();
        $this->_collection = Mage::getModel('igallery/banner')
                ->getCollection()->addEnableFilter($this->_isActive)
                        ->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_ASC);;
        if (!Mage::app()->isSingleStoreMode()) {
            $this->_collection->addStoreFilter($storeId);
        }

        return $this->_collection;
    }
    
    public function getGridColumns() {
        preg_match('/([1-3]col)/', Mage::app()->getLayout()->getBlock('root')->getTemplate(), $match);
        $columns = 3;
        switch ($match[0]) {
            case '2col':
                $columns = 4;
                break;

            case '1col':
                $columns = 6;
                break;
        }
        return $columns;
    }
}