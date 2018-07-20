<?php
/**
 * Class Easyship_Shipping_Model_Api2_Token_Rest_Admin_V1
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.5
 * Author URI: https://www.easyship.com
*/

class Easyship_Shipping_Model_Api2_Token_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Token
{
    /**
     * Override dispatch function to handle Post request for Entity type
     *
     */
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation() ) {
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
                if (!$this->_checkMethodExist('_create')) {
                    $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                }
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData) ) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                if ($this->getRequest()->isAssocArrayInRequestBody()) {
                    $this->_errorIfMethodNotExist('_create');
                    $filteredData = $this->getFilter()->in($requestData);
                    if (empty($filteredData)) {
                        $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                    }
                    $tokenData = $this->_create($filteredData);
                    $filteredData = $this->getFilter()->out($tokenData);
                    $this->_render($filteredData);
                }
                else {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                break;
            default:
                parent::dispatch(); // TODO: Change the autogenerated stub
        }

    }

    /**
     * Handle POST Request
     *
     * @param array
     *
     * @return array
     */
    public function _create(array $data)
    {
        $storeId = $this->getRequest()->getParam('store_id');

        if (!isset($storeId)) {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }
        return $this->_createToken($storeId, $data['token']);
    }
}
