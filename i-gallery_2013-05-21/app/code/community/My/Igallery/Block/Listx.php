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
class My_Igallery_Block_Listx extends Mage_Core_Block_Template {

    const XML_PATH_TEMPLATE = "igallery/general/template";
    const XML_PATH_IMAGE_LIMIT = 'igallery/general/image_limit';

    protected $_isDisabled = 0;
    protected $_collection;

    protected static $_gallery = array();

    public function getGallery() {
        $galleryId = $this->getGalleryId();
        if (array_key_exists($galleryId, self::$_gallery)) {
            return self::$_gallery[$galleryId];
        }
        self::$_gallery[$galleryId] = Mage::getModel("igallery/banner")->load($galleryId);
        return self::$_gallery[$galleryId];
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
        if ($gallery = $this->getGallery()) {
            $this->_collection->addFieldToFilter('banner_id', $gallery->getId());
        } else {
            $imageLimit = (int)Mage::getStoreConfig(self::XML_PATH_IMAGE_LIMIT);
            $this->_collection->setPageSize($imageLimit)->setCurPage(1);
        }
        return $this->_collection;
    }

    protected function _getColumnCount() {
        $count = 3;
        if ($gallery = $this->getGallery()) {
            $count = (int)$gallery->getColumnCount();
        }
        return $count;
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

    public function getTemplate() {
        if ($this->getData('template')) {
            return $this->getData('template');
        }
        return Mage::getStoreConfig(self::XML_PATH_TEMPLATE);
    }
}