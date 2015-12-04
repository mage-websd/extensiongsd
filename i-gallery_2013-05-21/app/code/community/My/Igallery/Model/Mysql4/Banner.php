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
 * Banner Resource Model
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->_init('igallery/banner', 'banner_id');
    }

    /**
     * Load images
     */
    public function loadImage(Mage_Core_Model_Abstract $object) {
        return $this->__loadImage($object);
    }

    /**
     * Load images
     */
    public function loadImageForFrontend(Mage_Core_Model_Abstract $object) {
        return $this->__loadImageForFrontend($object);
    }

    /**
     * Load thumbnail image
     */
    public function loadThumbnailImageForFrontend(Mage_Core_Model_Abstract $object) {
        return $this->__loadThumbnailImageForFrontend($object);
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassDelete()) {
            $object = $this->__loadStore($object);
            $object = $this->__loadImage($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($data = $object->getStoreId()) {
            $select->join(
                    array('store' => $this->getTable('igallery/banner_store')), $this->getMainTable().'.banner_id = `store`.banner_id')
                    ->where('`store`.store_id in (0, ?) ', $data);
        }
        $select->order('name DESC')->limit(1);

        return $select;
    }

    /**
     * Call-back function
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassStatus()) {
            $this->__saveToStoreTable($object);
            $this->__saveToImageTable($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * Call-back function
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        $adapter = $this->_getReadAdapter();
        // 1. Delete banner/store
        $adapter->delete($this->getTable('igallery/banner_store'), 'banner_id='.$object->getId());
        $adapter->delete($this->getTable('igallery/banner_image'), 'banner_id='.$object->getId());

        return parent::_beforeDelete($object);
    }

    /**
     * Load stores
     */
    private function __loadStore(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('igallery/banner_store'))
                ->where('banner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['store_id'];
            }
            $object->setData('store_id', $array);
        }
        return $object;
    }

    /**
     * Load images
     */
    private function __loadImage(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('igallery/banner_image'))
                ->where('banner_id = ?', $object->getId())
                ->order(array('position ASC', 'label'));
        $object->setData('image', $this->_getReadAdapter()->fetchAll($select));
        return $object;
    }

    /**
     * Load images
     */
    private function __loadImageForFrontend(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('igallery/banner_image'))
                ->where('banner_id = ?', $object->getId())
                ->where('disabled = 0');
        if ($object->getIsRandom() == '1') {
            $select->order(array(new Zend_Db_Expr('RAND()'),'label'));
        } else {
            $select->order(array('position ASC', 'label'));
        }
        $object->setData('front_image', $this->_getReadAdapter()->fetchAll($select));
        return $object;
    }

    /**
     * Load thumbnail Image
     */
    private function __loadThumbnailImageForFrontend(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('igallery/banner_image'))
                ->where('banner_id = ?', $object->getId())
                ->where('disabled = 0')
                //->where('thumb = 1')
                ->order(array('position ASC', 'label'));
        
        $object->setData('thumbnail_image', new My_Igallery_Model_Banner($this->_getReadAdapter()->fetchRow($select)));
        return $object;
    }
    /**
     * Save stores
     */
    private function __saveToStoreTable(Mage_Core_Model_Abstract $object) {
        if (!$object->getData('stores')) {
            $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
            $this->_getWriteAdapter()->delete($this->getTable('igallery/banner_store'), $condition);

            $storeArray = array(
                'banner_id' => $object->getId(),
                'store_id' => '0');
            $this->_getWriteAdapter()->insert(
                    $this->getTable('igallery/banner_store'), $storeArray);
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('igallery/banner_store'), $condition);
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array(
                'banner_id' => $object->getId(),
                'store_id' => $store);
            $this->_getWriteAdapter()->insert(
                    $this->getTable('igallery/banner_store'), $storeArray);
        }
    }

    /**
     * Save stores
     */
    private function __saveToImageTable(Mage_Core_Model_Abstract $object) {
        if ($_imageList = $object->getData('images')) {
            $_imageList = Zend_Json::decode($_imageList);
            if (is_array($_imageList) and sizeof($_imageList) > 0) {
                $_imageTable = $this->getTable('igallery/banner_image');
                $_adapter = $this->_getWriteAdapter();
                $_adapter->beginTransaction();
                try {
                    $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
                    $this->_getWriteAdapter()->delete($this->getTable('igallery/banner_image'), $condition);

                    foreach ($_imageList as &$_item) {
                        if (isset($_item['removed']) and $_item['removed'] == '1') {
                            $_adapter->delete($_imageTable, $_adapter->quoteInto('image_id = ?', $_item['value_id'], 'INTEGER'));
                        } else {
                            $_data = array(
                                'label'     => $_item['label'],
                                'file'      => $_item['file'],
                                'position'  => $_item['position'],
                                'disabled'  => $_item['disabled'],
                                'banner_id' => $object->getId());
                            $_adapter->insert($_imageTable, $_data);
                        }
                    }
                    $_adapter->commit();
                } catch (Exception $e) {
                    $_adapter->rollBack();
                    echo $e->getMessage();
                }
            }
        }
    }
}