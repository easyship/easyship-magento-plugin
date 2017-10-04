<?php

class Easyship_Shipping_Model_Api2_Token_Rest extends Easyship_Shipping_Model_Api2_Token
{
    protected function _create()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}