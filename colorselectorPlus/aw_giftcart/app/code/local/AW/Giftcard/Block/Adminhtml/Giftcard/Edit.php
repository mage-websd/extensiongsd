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

class AW_Giftcard_Block_Adminhtml_Giftcard_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'aw_giftcard';
        $this->_controller = 'adminhtml_giftcard';
        $this->_formScripts[] = "
            function saveAndContinueEdit(url) {
               editForm.submit(
                    url.replace(/{{tab_id}}/, aw_giftcard_info_tabsJsTabs.activeTab.id)
                );

            }"
        ;
        parent::__construct();
    }

    public function getHeaderText()
    {
        $title = $this->__('New Gift Card');
        $giftcardModel = Mage::registry('current_giftcard');
        if (null !== $giftcardModel->getId()) {
            $title = $this->__('Edit Gift Card');
        }
        return $title;
    }

    protected function _prepareLayout()
    {
        $this->_addButton('save_and_continue', array(
            'label' => $this->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
            'class' => 'save'
        ), 10);

        parent::_prepareLayout();
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true,
            'back' => 'edit',
            'tab' => '{{tab_id}}'
        ));
    }

}