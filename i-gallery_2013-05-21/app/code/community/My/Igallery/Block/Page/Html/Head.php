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
 * Page Head block
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    /**
     * Add HEAD Item First
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return My_Igallery_Block_Page_Html_Head
     */
    public function addFirst($type, $name, $params=null, $if=null, $cond=null)
    {
        $_item = array(
            $type.'/'.$name => array(
                'type'   => $type,
                'name'   => $name,
                'params' => $params,
                'if'     => $if,
                'cond'   => $cond)
            );
        $_head = $this->__getHeadBlock();
        if (is_object($_head)) {
            $_itemList = $_head->getData('items');
            $_itemList = array_merge($_item, $_itemList);

            $_head->setData('items', $_itemList);
        }
    }

    /**
     * Add HEAD Item before
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return My_Igallery_Block_Page_Html_Head
     */
    public function addBefore($type, $name, $before=null, $params=null, $if=null, $cond=null)
    {
        if ($before) {
            $_backItem = array();
            $_searchStatus = false;
            $_searchKey = $type.'/'.$before;
            $_head = $this->__getHeadBlock();
            if (is_object($_head)) {
                $_itemList = $_head->getData('items');
                if (is_array($_itemList)) {
                    $keyList = array_keys($_itemList);
                    foreach ($keyList as &$_key) {
                        if ($_searchKey == $_key) {
                            $_searchStatus = true;
                        }

                        if ($_searchStatus) {
                            $_backItem[$_key] = $_itemList[$_key];
                            unset($_itemList[$_key]);
                        }
                    }
                }

                if ($type==='skin_css' && empty($params)) {
                    $params = 'media="all"';
                }
                $_itemList[$type.'/'.$name] = array(
                    'type'   => $type,
                    'name'   => $name,
                    'params' => $params,
                    'if'     => $if,
                    'cond'   => $cond,
                );

                if (is_array($_backItem)) {
                    $_itemList = array_merge($_itemList, $_backItem);
                }
                $_head->setData('items', $_itemList);
            }
        }
    }

    /**
     * Add HEAD Item After
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return My_Igallery_Block_Page_Html_Head
     */
    public function addAfter($type, $name, $after=null, $params=null, $if=null, $cond=null)
    {
        if ($after) {
            $_backItem = array();
            $_searchStatus = false;
            $_searchKey = $type.'/'.$after;
            $_head = $this->__getHeadBlock();
            if (is_object($_head)) {
                $_itemList = $_head->getData('items');
                if (is_array($_itemList)) {
                    $keyList = array_keys($_itemList);
                    foreach ($keyList as &$_key) {
                        if ($_searchStatus) {
                            $_backItem[$_key] = $_itemList[$_key];
                            unset($_itemList[$_key]);
                        }
                        if ($_searchKey == $_key) {
                            $_searchStatus = true;
                        }
                    }
                }

                if ($type==='skin_css' && empty($params)) {
                    $params = 'media="all"';
                }
                $_itemList[$type.'/'.$name] = array(
                    'type'   => $type,
                    'name'   => $name,
                    'params' => null,
                    'if'     => null,
                    'cond'   => null,
                );

                if (is_array($_backItem)) {
                    $_itemList = array_merge($_itemList, $_backItem);
                }
                $_head->setData('items', $_itemList);
            }
        }
    }

    /*
     * Get head block
     */
    private function __getHeadBlock() {
        return Mage::getSingleton('core/layout')->getBlock('head');
    }
}