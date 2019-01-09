<?php
/**
 * Class Easyship_Shipping_Model_Api2_Shipment_Rest_Admin_V1
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 1.0.1
 * Author URI: https://www.easyship.com
*/
class Easyship_Shipping_Model_Api2_Shipment_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Shipment_Rest
{
    /**
     * Handle Post Request
     *
     * @param array
     * @return array
     */
    protected function _create(array $data)
    {
        $validator = Mage::getModel('easyship/api2_shipment_validator_shipment', array(
            'operation' => self::OPERATION_CREATE
        ));

        $orderIncrementId    = $this->getRequest()->getParam('orderincrementid');
        $data['shipment']['orderIncrementId'] = $orderIncrementId;

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $shipmentId = $this->_createShipment($data['shipment']['orderIncrementId'],
                $data['shipment']['itemsQty'], $data['shipment']['comment'],$data['shipment']['email'],
                $data['shipment']['includeComment']);

        if (!$shipmentId) {
            $this->_critical(Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        $collection = $this->_getCollectionForSingleRetrieve($shipmentId);
        $shipment = $collection->getItemById($shipmentId);

        if (!$shipment) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $this->getShipmentCreateRespond($shipment);

    }

    /**
     * Construct Shipment respond for Easyship API
     *
     * @param Vairen_Object
     * @return array
     */
    protected function getShipmentCreateRespond($shipment)
    {
        $shipmentData = $shipment->getData();
        $shipmentId = $shipment->getId();
        $items = $this->_getItems(array($shipmentId));
        $comments = $this->_getComments(array($shipmentId));
        $tracks = $this->_getTracks(array($shipmentId));

        $shipmentData['items'] = $items;
        $shipmentData['tracks'] = $track;
        $shipmentData['comments'] = $comments;
        return $shipmentData;
    }

    /**
     * Override dispatch method for unsupported method
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



}
