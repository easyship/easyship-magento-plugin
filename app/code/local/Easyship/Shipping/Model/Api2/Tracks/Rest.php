<?php

class Easyship_Shipping_Model_Api2_Tracks_Rest extends Easyship_Shipping_Model_Api2_Tracks 
{
    // Only Allow Admin role to call this API
    // create for POST
    protected function _create() 
    {
         $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}