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
 * Banner model
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Model_Banner extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('igallery/banner');
    }

    /*
     * Load image
     */
    public function getImageList() {
        if (!$this->hasData('image')) {
            $_object = $this->_getResource()->loadImage($this);
        }
        return $this->getData('image');
    }

    /*
     * Load image
     *
     */
    public function getImageListForFrontend() {
        if (!$this->hasData('front_image')) {
            $_object = $this->_getResource()->loadImageForFrontend($this);
        }
        return $this->getData('front_image');
    }

    /*
     * Load image
     *
     */
    public function getThumbnailImageListForFrontend() {
        if (!$this->hasData('thumbnail_image')) {
            $_object = $this->_getResource()->loadThumbnailImageForFrontend($this);
        }
        return $this->getData('thumbnail_image');
    }
}