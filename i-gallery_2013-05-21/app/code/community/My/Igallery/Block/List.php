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
 * List Block
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Block_List extends Mage_Core_Block_Template {
    const XML_PATH_IMAGE_LIMIT = 'igallery/general/image_limit';
    const XML_PATH_COLUMN_COUNT = 'igallery/general/column_count';
    const XML_PATH_IS_RANDOM = 'igallery/general/is_random';

    protected $_isDisabled = 0;
    protected $_collection;

    protected function _getGallery() {
        if (Mage::registry('current_igallery')) {
            return Mage::registry('current_igallery');
        }
        return null;
    }

    public function getCollection($position = null) {
        return $this->_getCollection($position);
    }

    protected function _getCollection($position = null) {
        if ($this->_collection) {
            return $this->_collection;
        }

        $this->_collection = Mage::getModel('igallery/banner_image')
                ->getCollection()->addEnableFilter($this->_isDisabled)
                        ->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);
        if (Mage::registry('current_igallery')) {
            $this->_collection->addFieldToFilter('banner_id', Mage::registry('current_igallery')->getId());
        } else {
            $imageLimit = (int)Mage::getStoreConfig(self::XML_PATH_IMAGE_LIMIT);
            $this->_collection->setPageSize($imageLimit)->setCurPage(1);

            $isRandom = (int)Mage::getStoreConfig(self::XML_PATH_IS_RANDOM);
            if ($isRandom) {
                $this->_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            }
        }
        return $this->_collection;
    }

    protected function _getColumnCount() {
        if (Mage::registry('current_igallery') and Mage::registry('current_igallery')->getColumnCount()) {
            return (int)Mage::registry('current_igallery')->getColumnCount();
        }
        if ($columnCount = (int)Mage::getStoreConfig(self::XML_PATH_COLUMN_COUNT)) {
            return $columnCount;
        }
        return 4;
    }

    /**
     * Wrapper for standart strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool $escape
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $escape = false)
    {
        $result = strip_tags($data, $allowableTags);
        return $escape ? $this->escapeHtml($result, $allowableTags) : $result;
    }

    /**
     * Escape html entities
     *
     * @param   mixed $data
     * @param   array $allowedTags
     * @return  mixed
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
 
        $toolbar = $this->getToolbarBlock();
 
        // called prepare sortable parameters
        $collection = $this->getCollection();
 
        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        $toolbar->setCollection($collection);
 
        $this->setChild('toolbar', $toolbar);
        $this->getCollection()->load();
        return $this;
    }
    public function getDefaultDirection(){
        return 'asc';
    }
    public function getAvailableOrders(){
        return array('image_id'=> 'ID','name'=>'Label','position'=>'Position');
    }
    public function getSortBy(){
        return 'position';
    }
    public function getToolbarBlock()
    {
        $block = $this->getLayout()->createBlock('igallery/list_toolbar', microtime());
        return $block;
    }
    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }
 
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
}