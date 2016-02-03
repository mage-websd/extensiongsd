<?php

    class Bc_Deliverydate_Helper_Data extends Mage_Core_Helper_Abstract
    {
        public function getFormatedDeliveryDate($date = null)
        {
            //if null or 0-0-0 00:00:00 return no date string
            if(empty($date) ||$date == null || $date == '0000-00-00 00:00:00'){
                return Mage::helper('deliverydate')->__("No Delivery Date Specified.");
            }

            //Format Date
            $formatedDate = Mage::helper('core')->formatDate($date, 'medium');
            //TODO: check that date is valid before passing it back

            return $formatedDate; 
        }

        public function getFormatedDeliveryDateToSave($date = null)
        {
            if(empty($date) ||$date == null || $date == '0000-00-00 00:00:00'){
                return null;
            }

            $timestamp = null;
            try{
                //TODO: add Better Date Validation
                $timestamp = strtotime($date);
                $dateArray = explode("-", $date);
                if(count($dateArray) != 3){
                    //invalid date
                    return null;
                }
                //die($timestamp."<<");
                //$formatedDate = date('Y-m-d H:i:s', strtotime($timestamp));
                //$formatedDate = date('Y-m-d H:i:s', mktime(0, 0, 0, $dateArray[0], $dateArray[1], $dateArray[2]));
                $formatedDate = date('Y-m-d H:i:s',strtotime($date));
            } catch(Exception $e){
                //TODO: email error 
                //return null if not converted ok
                return null;
            }                

            return $formatedDate;         
        }
        public function saveShippingArrivalDate($observer){

            $order = $observer->getEvent()->getOrder();
            if (Mage::getStoreConfig('deliverydate/deliverydate_general/on_which_page')==2){
                $desiredArrivalDate = Mage::helper('deliverydate')->getFormatedDeliveryDateToSave(Mage::app()->getRequest()->getParam('shipping_arrival_date'));
                if (isset($desiredArrivalDate) && !empty($desiredArrivalDate)){
                    $order->setShippingArrivalComments(Mage::app()->getRequest()->getParam('shipping_arrival_comments'));
                    $order->setShippingArrivalDate($desiredArrivalDate);
                }
            }else{
                $cart = Mage::getModel('checkout/cart')->getQuote()->getData();
                $desiredArrivalDate = Mage::helper('deliverydate')->getFormatedDeliveryDateToSave($cart['shipping_arrival_date']);
                $shipping_arrival_comments = $cart['shipping_arrival_comments'];
                if (isset($desiredArrivalDate) && !empty($desiredArrivalDate)){
                    $order->setShippingArrivalComments($shipping_arrival_comments);
                    $order->setShippingArrivalDate($desiredArrivalDate);
                }
            }
        }
        public function saveShippingArrivalDateAdmin($observer){

            $order = $observer->getEvent()->getOrder();
            $cart = Mage::app()->getRequest()->getParams();
            $desiredArrivalDate = Mage::helper('deliverydate')->getFormatedDeliveryDateToSave($cart['shipping_arrival_date_display']);
            $shipping_arrival_comments = $cart['shipping_arrival_comments'];
            if (isset($desiredArrivalDate) && !empty($desiredArrivalDate)){
                $order->setShippingArrivalComments($shipping_arrival_comments);
                $order->setShippingArrivalDate($desiredArrivalDate);
            }

        }

}