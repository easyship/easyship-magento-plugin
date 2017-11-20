<?php
/**
 * Class Easyship_Shipping_Model_Api2_Shipment_Rest
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.0
 * Autho URI: https://www.easyship.com
*/

class Easyship_Shipping_Model_Api2_Shipment_Rest extends Easyship_Shipping_Model_Api2_Shipment
{
    // Only Allow Admin role to call this API
    // create for POST
    protected function _create(array $data)
    {
         $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}
