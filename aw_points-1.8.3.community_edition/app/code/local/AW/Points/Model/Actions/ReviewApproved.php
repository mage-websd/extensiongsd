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
 * @package    AW_Points
 * @version    1.8.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Model_Actions_ReviewApproved extends AW_Points_Model_Actions_Abstract
{
    protected $_action = 'review_approved';
    protected $_comment = 'Reward for reviewing product %s';

    /**
     * @return Mage_Review_Model_Review
     */
    protected function _getReview()
    {
        return $this->getObjectForAction();
    }

    protected function _limitAmountByDay($amount)
    {
        $reviewStoreId = $this->_getReview()->getStoreId();
        $pointLimitForAction = $this->_getConfigHelper()->getPointsLimitForReviewingProduct($reviewStoreId);

        $collection = Mage::getModel('points/transaction')
            ->getCollection()
            ->addFieldToFilter('summary_id', $this->getSummary()->getId())
            ->addFieldToFilter('action', $this->getAction())
            ->limitByDay(Mage::getModel('core/date')->gmtTimestamp());

        /* Current summ getting */
        $summ = 0;
        foreach ($collection as $transaction) {
            $summ += $transaction->getBalanceChange();
        }
        return $this->_calculateNewAmount($summ, $amount, $pointLimitForAction);
    }

    protected function _limitAmountByWordsCount($amount)
    {
        $minimumWordsCount = $this->_getConfigHelper()->getMinumumWordsCountInReview();
        if ($minimumWordsCount) {
            $reviewContent = $this->_getReview()->getData('detail');
            $wordsCount = count(preg_split('/\s+/mu', trim($reviewContent)));
            return $wordsCount >= $minimumWordsCount
                ? $amount
                : 0;
        }
        return $amount;
    }

    protected function _applyLimitations($amount)
    {
        if ($newAmount = $this->_limitAmountByWordsCount($amount)) {
            $newAmount = $this->_limitAmountByDay($newAmount);
        }
        return parent::_applyLimitations($newAmount);
    }

    public function getCommentHtml($area = self::ADMIN)
    {
        $comment = $this->_transaction->getComment();
        $productId = array_slice($comment, -1, 1);
        $productId = (int)array_shift($productId);
        if ($productId != 0) {
            $comment[1] = $this->_getLinkHtml($productId, $area);
        }
        if ($area == self::ADMIN) {
            return is_array($comment) ?
                    call_user_func_array(array($this->_getHelper(), '__'), $comment) :
                    $this->_getHelper()->__($comment);
        } else {
            $locale = Mage::getStoreConfig('general/locale/code', $this->_transaction->getStoreId());
            return $this->_translateComment($comment, $locale);
        }
    }

    public function getComment()
    {
        if (isset($this->_commentParams['product_name'])) {
            return array(
                $this->_comment,
                $this->_commentParams['product_name'],
                $this->_commentParams['product_id']
            );
        }
        return $this->_comment;
    }

    protected function _getLinkHtml($productId, $area)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($area == self::ADMIN) {
            $productUrl = Mage::getModel('adminhtml/url')->getUrl(
                'adminhtml/catalog_product/edit',
                array('id' => $productId)
            );
        } else {
            $productUrl = $product->getProductUrl();
        }
        return "<a href='{$productUrl}'>{$product->getName()}</a>";
    }
}
