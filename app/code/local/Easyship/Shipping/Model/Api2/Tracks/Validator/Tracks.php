<?php
/**
 * Class Easyship_Shipping_Model_Api2_Tracks_Validator_Tracks
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 1.0.0
 * Author URI: https://www.easyship.com
 */

class Easyship_Shipping_Model_Api2_Tracks_Validator_Tracks extends Mage_Api2_Model_Resource_Validator
{
    protected $_operation = null;

    /**
     * constructor
     */
    public function __construct($options)
    {
        if (!isset($options['operation']) || empty($options['operation'])) {
            Mage::throwException("Passed parameter 'operation' is empty");
        }

        $this->_operation = $options['operation'];
    }

    /**
     * Validate input data
     *
     * @param array
     *
     * @return boolean
     */
    public function isValidData(array $data)
    {
        try {
            $this->_validateAttributeSet($data);
            $this->_validateOrderIncrementId($data);
            $this->_validateShipmentIncrementId($data);
            $this->_validateCarrier($data);
            $this->_validateTitle($data);
            $this->_valiedateTrackNumber($data);
            $isStatisfied = count($this->getErrors()) == 0;
        } catch (Mage_Api2_Exception $e) {
            $this->_addError($e->getMessage());
            $isStatisfied = false;
        }

        return $isStatisfied;
    }

    /**
     * Validate Attributes set
     *
     * @param array
     *
     */
    protected function _validateAttributeSet($data)
    {
        if (!isset($data['track']) || empty($data['track'])) {
            $this->_critical('Missing Track in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!isset($data['track']['shipmentIncrementId']) || empty($data['track']['shipmentIncrementId'])) {
            $this->_critical('Missing Shipment Increment Id in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!isset($data['track']['carrier']) || empty($data['track']['carrier'])) {
            $this->_critical('Missing Carrier Increment Id in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!isset($data['track']['title']) || empty($data['track']['title'])) {
            $this->_critical('Missing Title in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!isset($data['track']['trackNumber']) || empty($data['track']['trackNumber'])) {
            $this->_critical('Missing Shipment Track Number in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate Order Increment Id if exist
     */
    protected function _validateOrderIncrementId($data)
    {
        $shipmentIncrementId = $data['track']['shipmentIncrementId'];
        $orderIncrementId = $data['track']['orderIncrementId'];
        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);
        if (!is_string($orderIncrementId)) {
            $this->_critical('Order Increment Id is not a string in request.',
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if ($shipment->getOrder()->getIncrementId() != $orderIncrementId) {
            $this->_critical('Order Increment Id does not match', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate if Shipment Increment Id exist
     */
    protected function _validateShipmentIncrementId($data)
    {
        $shipmentIncrementId = $data['track']['shipmentIncrementId'];

        if (!is_string($shipmentIncrementId)) {
            $this->_critical('Shipment Incremend Id is not a string in request',
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);

        if (!$shipment->getId()) {
            $this->_critical('Shipment does not exist.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate if carrier is string
     * @param array
     */
    protected function _validateCarrier($data)
    {
        $carrier = $data['track']['carrier'];

        if (!is_string($carrier)) {
            $this->addError('carrier is not a string in request');
        }
    }

    /**
     * Validate if Tracking Number is string
     * @param array
     */
    protected function _valiedateTrackNumber($data)
    {
        $number = $data['track']['trackNumber'];

        if (!is_string($number)) {
            $this->addError('trackNumber is not a string in request');
        }
    }

    /**
     * Validate if Title is string
     * @param array
     */
    protected function _validateTitle($data)
    {
        $title = $data['track']['title'];

        if (!is_string($title)) {
            $this->addError('title is not a string in request');
        }
    }

    /**
     * Throw execption with message if validation fail
     *
     */
    protected function _critical($message, $code)
    {
        throw new Mage_Api2_Exception($message, $code);
    }
}
