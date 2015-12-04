<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Giftcard
 * @version    1.0.8
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Giftcard_Block_Adminhtml_Catalog_Product_Render_Amount extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element = null;
    protected $_websites = null;

    public function __construct()
    {
        $this->setTemplate('aw_giftcard/catalog/product_renderer_amount.phtml');
    }

    public function isMultiWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getWebsites()
    {
        if (null === $this->_websites) {
            $websites = array();
            $websites[0] = array(
                'name'      => $this->__('All Websites'),
                'currency'  => Mage::app()->getBaseCurrencyCode()
            );

            if ($this->isMultiWebsites() && !$this->getElement()->getEntityAttribute()->isScopeGlobal()) {
                if ($storeId = $this->getProduct()->getStoreId()) {
                    $website = Mage::app()->getStore($storeId)->getWebsite();
                    $websites[$website->getId()] = array(
                        'name'      => $website->getName(),
                        'currency'  => $website->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                    );
                } else {
                    foreach (Mage::app()->getWebsites() as $website) {
                        if (!in_array($website->getId(), $this->getProduct()->getWebsiteIds())) {
                            continue;
                        }
                        $websites[$website->getId()] = array(
                            'name'      => $website->getName(),
                            'currency'  => $website->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                        );
                    }
                }
            }
            $this->_websites = $websites;
        }
        return $this->_websites;
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $this->setChild(
            'add_button',
            $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'     => $this->__('Add Amount'),
                        'onclick'   => "giftcardAmountsControl.addItem('" . $this->getElement()->getHtmlId() . "')",
                        'class'     => 'add'
                    )
                )
        );
        return $this->toHtml();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getValues()
    {
        return $this->getElement()->getValue();
    }
}