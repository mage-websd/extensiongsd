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
 * Layout generat observer
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */

class My_Igallery_Model_Layout_Generate_Observer {
    const XML_PATH_ENABLE_JQUERY = 'igallery/general/enable_jquery';

    /**
     * Add Jquery library depends on configuration value
     * @return int $count
     */
	public function addJqueryLibrary($observer) {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        $enableJquery = Mage::getStoreConfig(self::XML_PATH_ENABLE_JQUERY);
        if ($enableJquery == 1) {
            $_head = $this->__getHeadBlock();
            if ($_head) {
                $_head->addFirst('js', 'my_igallery/jquery.js');
                $_head->addAfter('js', 'my_igallery/jquery.noconflict.js', 'my_igallery/jquery.js');
            }
        }
	}

    /*
     * Get head block
     */
    private function __getHeadBlock() {
        return Mage::getSingleton('core/layout')->getBlock('my_igallery_head');
    }
}