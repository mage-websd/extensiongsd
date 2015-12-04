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
class My_Igallery_Block_Widget_List extends My_Igallery_Block_Listx 
    implements Mage_Widget_Block_Interface {

    const OPTION_YES = 1;
    const OPTION_NO = 2;

    public function getTemplate() {
        if ($this->getData('template')) {
            return $this->getData('template');
        }
        return 'my_igallery/widget.phtml';
    }
    
    protected function _getCollection($position = null) {
        if ($this->_collection) {
            return $this->_collection;
        }

        $this->_collection = Mage::getModel('igallery/banner_image')
                ->getCollection()->addEnableFilter($this->_isDisabled)
                        ->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);
        $gallery = $this->getGallery();
        $this->_collection->addFieldToFilter('banner_id', $gallery->getId());
        $this->_collection->setPageSize($this->getImageLimit())->setCurPage(1);    
        
        return $this->_collection;
    }

    public function getShowTitle() {
        return $this->getGalleryTitleVisible() == self::OPTION_YES;
    }
    
    public function getShowDescription() {
        return $this->getGalleryDescriptionVisible() == self::OPTION_YES;
    }
}