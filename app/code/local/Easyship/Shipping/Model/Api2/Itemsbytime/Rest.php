<?php

class Easyship_Shipping_Model_Api2_Itemsbytime_Rest extends Easyship_Shipping_Model_Api2_Items
{
    const BASE_DATETIME_FORMAT = 'Y-m-d H:i:s';

    protected function _retrieve()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

    protected function _retrieveCollection()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['created_at_from']) || empty($params['created_at_to'])) {
            $this->_critical('Please set created_at_from and created_at_to', 400);
        }

        if (!$this->_validateDate($params['created_at_from']) || !$this->_validateDate($params['created_at_to'])) {
            $this->_critical('Invalid date format, please give date to this type -  Y-m-d H:i:s ', 400);
        }

        $orders = $this->getOrdersByData($params['created_at_from'], $params['created_at_to']);

        if ($orders->getSize() == 0) {
            return $orders->load()->toArray()['items'];
        }

        $ordersData = [];

        foreach ($orders as $order) {
            $orderId = $order->getId();
            $orderData = $order->getData();
            $addresses = $this->_getAddresses(array($orderId));
            $items     = $this->_getItems(array($orderId));
            $shipments = $this->_getShipment(array($orderId));
            $payments  = $this->_getPayment(array($orderId));
            $status    = $this->_getStatusHistory(array($orderId));
            $orderData['order_id'] = $orderId;
            if ($addresses) {

                for ($i=0; $i < count($addresses[$orderId]); $i++) {
                    $address = $addresses[$orderId][$i];
                    if ($address['address_type'] == 'billing') {
                        $orderData['billing_firstname'] = $address['firstname'];
                        $orderData['billing_lastname'] = $address['lastname'];
                        if ($address['name']) {
                            $orderData['billing_name'] = $address['name'];
                        }
                    }
                    else if ($address['address_type'] == 'shipping') {
                        $orderData['shipping_firstname'] = $address['firstname'];
                        $orderData['shipping_lastname'] = $address['lastname'];
                        $orderData['shipping_address'] = $address;
                        if ($address['name']) {
                            $orderData['shipping_name'] = $address['name'];
                        }
                    }
                }
            }
            if ($items) {
                $orderData['items'] = $items[$orderId];
            }

            if ($payments) {
                $orderData['payment'] = $payments[$orderId];
            }

            if ($status) {
                $orderData['status_history'] = $status[$orderId];
            }

            if ($shipments) {
                $orderData['shipments'] = $shipments[$orderId];
            }
            else {
                $orderData['shipments'] = array();
            }

            array_push($ordersData, $orderData);
        }

        return $ordersData;
    }

    /**
     * Validate date
     * @param string $date
     * @param string $format
     * @return bool
     */
    protected function _validateDate($date, $format = self::BASE_DATETIME_FORMAT)
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Get orders by date range
     * @param $from
     * @param $to
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function getOrdersByData($from, $to)
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('updated_at', array('from' => $from, 'to' => $to));

        return $collection;
    }
}
