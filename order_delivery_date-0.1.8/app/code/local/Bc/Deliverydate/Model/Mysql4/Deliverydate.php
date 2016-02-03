<?php

class Bc_Deliverydate_Model_Mysql4_Deliverydate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the deliverydate_id refers to the key field in your database table.
        $this->_init('deliverydate/deliverydate', 'deliverydate_id');
    }
}