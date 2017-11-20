<?php
/**
 * Class Easyship_Shipping_Model_Api2_Tracks_Rest_Admin_V1
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.0
 * Autho URI: https://www.easyship.com
*/

class Easyship_Shipping_Model_Api2_Tracks_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Tracks_Rest
{

    /**
     * Override dispatch function for API Request
     *
     */
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
            /* Create */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
                if (!$this->_checkMethodExist('_create')) {
                    $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                }
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                if ($this->getRequest()->isAssocArrayInRequestBody()) {
                    $this->_errorIfMethodNotExist('_create');
                    $filteredData = $this->getFilter()->in($requestData);
                    if (empty($filteredData)) {
                        $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                    }
                    $shipmentData = $this->_create($filteredData);
                    $filteredData = $this->getFilter()->out($shipmentData);
                    $this->_render($filteredData);
                }
                else {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                break;
            default:
                parent::dispatch();
        }
    }

    /**
     * HANDLE POST Request for Track API
     *
     * @param array
     *
     * @return array
     */
    public function _create(array $data)
    {

        $orderIncrementId    = $this->getRequest()->getParam('orderincrementid');
        $data['track']['orderIncrementId'] = $orderIncrementId;
        $shipmentIncrementId = $this->getRequest()->getParam('shipmentincrementid');
        $data['track']['shipmentIncrementId'] = $shipmentIncrementId;

        $validator = Mage::getModel('easyship/api2_tracks_validator_tracks', array(
            'operation' => self::OPERATION_CREATE
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                 $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $track = $this->_createTracks($data['track']['shipmentIncrementId'], $data['track']['carrier'],
            $data['track']['title'], $data['track']['trackNumber']);

        $trackData = array();
        $trackData['track'] = array();

        $trackData['track']['shipment_id'] = $track['parent_id'];
        $trackData['track']['created_at'] = $track['created_at'];
        $trackData['track']['updated_at'] = $track['updated_at'];
        $trackData['track']['carrier_code'] = $track['carrier_code'];
        $trackData['track']['title'] = $track['title'];
        $trackData['track']['number'] = $track['number'];
        $trackData['track']['order_id'] = $track['order_id'];
        $trackData['track']['track_id'] = $track['entity_id'];
        return $track;

    }
}
