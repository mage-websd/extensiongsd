<?php
    /**
    * CustomSearch Observer
    *
    * @category    Exxex
    * @package     Essex_Customermod
    * @author      Sharpdotinc.com
    */
    class Bc_Deliverydate_Model_Observer
    {				

        public function checkout_controller_onepage_save_shipping_method($observer)
        {
            if (Mage::getStoreConfig('deliverydate/deliverydate_general/on_which_page')==1){
                $request = $observer->getEvent()->getRequest();
                $quote =  $observer->getEvent()->getQuote();

                $desiredArrivalDate = Mage::helper('deliverydate')->getFormatedDeliveryDateToSave($request->getPost('shipping_arrival_date', ''));
                if (isset($desiredArrivalDate) && !empty($desiredArrivalDate)){
                    $quote->setShippingArrivalDate($desiredArrivalDate);
                    $quote->setShippingArrivalComments($request->getPost('shipping_arrival_comments'));
                    $quote->save();
                }
            }

            return $this;
        }


}