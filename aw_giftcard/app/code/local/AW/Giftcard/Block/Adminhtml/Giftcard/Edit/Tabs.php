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

class AW_Giftcard_Block_Adminhtml_Giftcard_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('aw_giftcard_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Gift Card'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('info_tab', array(
           'label'     => $this->__('Information'),
           'content'   => $this->getLayout()
                            ->createBlock('aw_giftcard/adminhtml_giftcard_edit_tab_information')
                            ->initForm()
                            ->toHtml(),
           'active'    => true
        ));

        $giftcardModel = Mage::registry('current_giftcard');
        if ($giftcardModel->getId()) {
            $activeTab = $this->getRequest()->getParam('tab', null);
            $state = false;

            if ($activeTab == 'aw_giftcard_info_tabs_history_tab') {
                $state = true;
            }

            $this->addTab('history_tab', array(
                'label'     => $this->__('History'),
                'content'   => $this->getLayout()
                                ->createBlock('aw_giftcard/adminhtml_giftcard_edit_tab_history')
                                ->toHtml(),
                'active'    => $state
            ));
        }
        return parent::_beforeToHtml();
    }
}