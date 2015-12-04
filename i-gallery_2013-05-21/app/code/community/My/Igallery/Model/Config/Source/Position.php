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
 * Position config model
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Model_Config_Source_Position
{
    const LEFT_TOP          = 'LEFT_TOP';
    const LEFT_BOTTOM       = 'LEFT_BOTTOM';
    const CONTENT_TOP       = 'CONTENT_TOP';
    const CONTENT_BOTTOM    = 'CONTENT_BOTTOM';
    const RIGHT_TOP         = 'RIGHT_TOP';
    const RIGHT_BOTTOM      = 'RIGHT_BOTTOM';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::LEFT_TOP, 'label'=>Mage::helper('adminhtml')->__('Left Top')),
            array('value' => self::LEFT_BOTTOM, 'label'=>Mage::helper('adminhtml')->__('Left Bottom')),
            array('value' => self::CONTENT_TOP, 'label'=>Mage::helper('adminhtml')->__('Content Top')),
            array('value' => self::CONTENT_BOTTOM, 'label'=>Mage::helper('adminhtml')->__('Content Bottom')),
            array('value' => self::RIGHT_TOP, 'label'=>Mage::helper('adminhtml')->__('Right Top')),
            array('value' => self::RIGHT_BOTTOM, 'label'=>Mage::helper('adminhtml')->__('Right Bottom'))
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toGridOptionArray()
    {
        return array(
            self::LEFT_TOP => Mage::helper('adminhtml')->__('Left Top'),
            self::LEFT_BOTTOM => Mage::helper('adminhtml')->__('Left Bottom'),
            self::CONTENT_TOP => Mage::helper('adminhtml')->__('Content Top'),
            self::CONTENT_BOTTOM => Mage::helper('adminhtml')->__('Content Bottom'),
            self::RIGHT_TOP => Mage::helper('adminhtml')->__('Right Top'),
            self::RIGHT_BOTTOM => Mage::helper('adminhtml')->__('Right Bottom')
        );
    }
}