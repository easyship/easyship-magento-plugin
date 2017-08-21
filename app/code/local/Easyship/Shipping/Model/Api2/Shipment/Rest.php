<?php

class Easyship_Shipping_Model_Api2_Shipment_Rest extends Easyship_Shipping_Model_Api2_Shipment 
{
    // Only Allow Admin role to call this API
    // create for POST
    protected function _create(array $data) 
    {
         $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}