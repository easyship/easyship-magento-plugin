<?php

class Easyship_Shipping_Model_Api2_Shipment_Validator_Shipment extends Mage_Api2_Model_Resource_Validator
{
    protected $_operation = null;

    public function __construct($options)
    {
        if (!isset($options['operation']) || empty($options['operation'])) {
            throw new Exception("Passed parameter 'operation' is empty");
        }
        $this->_operation = $options['operation'];
    }

    public function isValidData(array $data)
    {
        try {
            $this->_validateAttributeSet($data);
            $this->_validateOrderIncrementId($data);
            $this->_validateOrderItemQty($data);
            $this->_validateComment($data);
            $this->_validateEmail($data);
            $this->_validateIncludeCommnet($data);
            $isStatisfied = count($this->getErrors()) == 0; 
        }
        catch (Mage_Api2_Exception $e) {
            $this->_addError($e->getMessage());
            $isStatisfied = false;
        }

        return $isStatisfied;
    }

    protected function _validateAttributeSet($data)
    {
        if (!isset($data['shipment']) || empty($data['shipment'])) {
            $this->_critical('Missing Shipment attribute in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!isset($data['shipment']['orderIncrementId']) || empty($data['shipment']['orderIncrementId'])) {
            $this->_critical('Missing orderIncrementId in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    protected function _validateOrderIncrementId($data)
    {
        $orderIncrementId = $data['shipment']['orderIncrementId'];

        if (!is_string($orderIncrementId)) {
            $this->_critical('Order Increment Id is not a string in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST );
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        if (!$order->getId()) {
            $this->_critical('Order does not exist.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!$order->canShip()) {
            $this->_critical('Cannot do shipment for order.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    protected function _validateOrderItemQty($data)
    {
        $orderItemQty = $data['shipment']['itemQty'];

        // optional
        if (!isset($orderItemQty)) {
            return;
        }

        // check if this is an array
        if (!is_array($orderItemQty)) {
            $this->_critical('Invalid type for parameter itemsQty.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        foreach ($orderItmeQty as $item) {
            if (!isset($item->order_item_id) || !isset($item->qty)) {
                $this->_addError('Warning: itemsQty object is invalid');
            }
        }
    }

    protected function _validateComment($data)
    {
        $comment = $data['shipment']['comment'];

        if (!isset($comment)) {
            return;
        }

        if (!is_string($comment)) {
            $this->_addError('Wrong data type for comment');
        }
    }

    protected function _validateEmail($data)
    {
        $email = $data['shipment']['email'];

        if (!isset($email)) {
            return;
        }

        if (!is_string($email) || !Zend_Validate::is($email, 'EmailAddress')) {
            $this->_addError('Wrong data type for email address');
        }
    }

    protected function _validateIncludeCommnet($data)
    {
        $includeComment = $data['shipment']['includeComment'];

        if (!isset($includeComment)) {
            return;
        }

        if (!is_int($includeComment)) {
            $this->_addError('Wrong data type for includeComment');
        }
    }

    protected function _critical($message, $code)
    {
        throw new Mage_Api2_Exception($message, $code);
    }

}