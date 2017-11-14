<?php
/** 
 * Class Easyship_Shipping_Model_Api2_Items_Rest
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.0
 * Autho URI: https://www.easyship.com 
*/
class Easyship_Shipping_Model_Api2_Items_Rest extends Easyship_Shipping_Model_Api2_Items
{
    // Only Allow Admin role to call this API
    // retrieve
    protected function _retrieve() 
    {
         $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}