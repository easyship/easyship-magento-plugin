<?php

class Easyship_Shipping_Model_Api2_Shipping_Rest extends Easyship_Shipping_Model_Api2_Shipping
{
     // Only Allow Admin role to call this API
    // retrieve
    protected function _retrieve() 
    {
         $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

}