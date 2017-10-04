<?php

class Easyship_Shipping_Model_Api2_Token extends Mage_Api2_Model_Resource
{
    protected function _createToken($storeId, $token)
    {
        $tokenPath = 'easyship_options/ec_shipping/store_' . $storeId . '_token';
        $enablePath = 'easyship_options/ec_shipping/store_' . $storeId . '_isRateEnabled';
        $activatePath = 'easyship_options/ec_shipping/store_' . $storeId . '_isExtActive';

        $encToken = Mage::helper('core')->encrypt($token);
        Mage::getConfig()->saveConfig($tokenPath, $encToken, 'default', 0);
        Mage::getConfig()->saveConfig($enablePath, '1', 'default', 0);
        Mage::getConfig()->saveConfig($activatePath, '1', 'default', 0);

        // return empty respond
        return array();
    }
}