<?php
/**
 * Class Easyship_Shipping_Model_Api2_Shipment_Validator_Shipment
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.3
 * Author URI: https://www.easyship.com
*/

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

    /**
     * Validate the input body
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

    /**
     * Validate the attribute set
     * @param array
     */
    protected function _validateAttributeSet($data)
    {
        if (!isset($data['shipment'])) {
            $this->_critical('Missing Shipment attribute in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!isset($data['shipment']['orderIncrementId']) || empty($data['shipment']['orderIncrementId'])) {
            $this->_critical('Missing orderIncrementId in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate Order Incremnet Id
     * @param array
     */
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

    /**
     * Validate if OrderItemQty is number
     * @param array
     */
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


    /**
     * Validate if Comment is string
     * @param array
     */
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

    /**
     * Validate Email Address
     * @param array
     */
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

    /**
     * Valudate if includecomment is integer
     */
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

    /**
     * throw exception with message if validation fail
     */
    protected function _critical($message, $code)
    {
        throw new Mage_Api2_Exception($message, $code);
    }

}
