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
 * Banner Helper
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_BASE = 'igallery/gallery/';

    /*
     * Get image url of a banner
     */
    public function getConfig($name = null) {
        return Mage::getStoreConfig(self::XML_PATH_BASE . $name);
    }

    /*
     * Get image url of a banner
     */
    public function getImageUrl($url = null) {
        return Mage::getSingleton('igallery/config')->getBaseMediaUrl() . $url;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }

    /*
     * Get banner
     */
    public function getBanner($bannerId = null) {
        return Mage::getSingleton('core/layout')->createBlock('igallery/bannerx')->setBannerId($bannerId)->toHtml();
    }

    public function getImageSize($image = null, $_maxW = 125, $_maxH = 125, $fix = false) {
        $_baseSrc = Mage::getSingleton('igallery/config')->getBaseMediaPath();
        if (file_exists($_baseSrc . $image->getFile())) {
            $_imageObject = new Varien_Image($_baseSrc . $image->getFile());
            $_sizeArray = array($_imageObject->getOriginalWidth(), $_imageObject->getOriginalHeight());
            $_defaultW = $_maxW;
            $_defaultH = $_maxH;
            if ($_sizeArray[0] / $_sizeArray[1] > $_defaultW / $_defaultH) {
                $_defaultW *= ( floatval($_sizeArray[0] / $_sizeArray[1]) / floatval($_defaultW / $_defaultH));
            } else {
                $_defaultH *= ( floatval($_defaultW / $_defaultH) / floatval($_sizeArray[0] / $_sizeArray[1]));
            }

            if ($fix == 'width') {
                if ($_defaultW > $_maxW) {
                    $_defaultH *= $_maxW / $_defaultW;
                    $_defaultW = $_maxW;
                }
            } elseif ($fix == 'height') {
                if ($_defaultH > $_maxH) {
                    $_defaultW *= $_maxH / $_defaultH;
                    $_defaultH = $_maxH;
                }
            } else {
                if ($_defaultW > $_maxW) {
                    $_defaultH *= $_maxW / $_defaultW;
                    $_defaultW = $_maxW;
                } elseif ($_defaultH > $_maxH) {
                    $_defaultW *= $_maxH / $_defaultH;
                    $_defaultH = $_maxH;
                }
            }

            return new Varien_Object(array(
                'width' => round($_defaultW),
                'height' => round($_defaultH)
            ));
        }
        return false;
    }
}