<?php
/**
* Inchoo
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
* Please do not edit or add to this file if you wish to upgrade
* Magento or this extension to newer versions in the future.
** Inchoo *give their best to conform to
* "non-obtrusive, best Magento practices" style of coding.
* However,* Inchoo *guarantee functional accuracy of
* specific extension behavior. Additionally we take no responsibility
* for any possible issue(s) resulting from extension usage.
* We reserve the full right not to provide any kind of support for our free extensions.
* Thank you for your understanding.
*
* @category Inchoo
* @package SocialConnect
* @author Marko Martinović <marko.martinovic@inchoo.net>
* @copyright Copyright (c) Inchoo (http://inchoo.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

class Inchoo_SocialConnect_FacebookController extends Inchoo_SocialConnect_Controller_Abstract
{

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        Mage::helper('inchoo_socialconnect/facebook')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Facebook account from our store account.')
            );
    }

    protected function _connectCallback() {
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');

        if(!($errorCode || $code) && !$state) {
            // Direct route access - deny
            return $this;
        }
        /*if(!$state || $state != Mage::getSingleton('core/session')->getFacebookCsrf()) {
            return $this;
        }*/
        
        if($errorCode) {
            // Facebook API read light - abort
            if($errorCode === 'access_denied') {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Facebook Connect process aborted.')
                    );

                return $this;
            }

            throw new Exception(
                sprintf(
                    $this->__('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );
        }
        
        if ($code) {
            /** @var Inchoo_SocialConnect_Helper_Facebook $helper */
            $helper = Mage::helper('inchoo_socialconnect/facebook');

            // Facebook API green light - proceed
            /** @var Inchoo_SocialConnect_Model_Facebook_Info $info */
            $info = Mage::getModel('inchoo_socialconnect/facebook_info');

            $token = $info->getClient()->getAccessToken($code);

            $info->load();

            $customersByFacebookId = $helper->getCustomersByFacebookId($info->getId());

            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if($customersByFacebookId->getSize()) {
                    // Facebook account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your Facebook account is already connected to one of our store accounts.')
                        );

                    return $this;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                $helper->connectByFacebookId(
                    $customer,
                    $info->getId(),
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your Facebook account is now connected to your store account. You can now login using '.
                        'our Facebook Login button or using store account credentials you will receive to your email '.
                        'address.')
                );

                return $this;
            }

            if($customersByFacebookId->getSize()) {
                // Existing connected user - login
                $customer = $customersByFacebookId->getFirstItem();

                $helper->loginByCustomer($customer);

                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your Facebook account.')
                    );

                return $this;
            }

            $customersByEmail = $helper->getCustomersByEmail($info->getEmail());

            if($customersByEmail->getSize()) {
                // Email account already exists - attach, login
                $customer = $customersByEmail->getFirstItem();

                $helper->connectByFacebookId(
                    $customer,
                    $info->getId(),
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at our store. Your Facebook account is '.
                        'now connected to your store account.')
                );

                return $this;
            }

            // New connection - create, attach, login
            $firstName = $info->getFirstName();
            if(empty($firstName)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Facebook first name. Please try again.')
                );
            }

            $lastName = $info->getLastName();
            if(empty($lastName)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Facebook last name. Please try again.')
                );
            }

            $birthday = $info->getBirthday();
            $birthday = Mage::app()->getLocale()->date($birthday, null, null, false)
                ->toString('yyyy-MM-dd');

            $gender = $info->getGender();
            if(empty($gender)) {
                $gender = null;
            } else if($gender = 'male') {
                $gender = 1;
            } else if($gender = 'female') {
                $gender = 2;
            }

            $helper->connectByCreatingAccount(
                $info->getEmail(),
                $info->getFirstName(),
                $info->getLastName(),
                $info->getId(),
                $birthday,
                $gender,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Facebook account is now connected to your new user account at our store.'.
                    ' Now you can login using our Facebook Login button.')
            );
            return $this->_redirect(Mage::getUrl('customer/account'));
        }
    }

}