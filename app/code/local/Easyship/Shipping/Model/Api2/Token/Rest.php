<?php
/**
 * Class Easyship_Shipping_Model_Api2_Token_Rest
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.4
 * Author URI: https://www.easyship.com
*/

class Easyship_Shipping_Model_Api2_Token_Rest extends Easyship_Shipping_Model_Api2_Token
{
    /**
     * Only Adminuser can call this API
     */
    protected function _create()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}
