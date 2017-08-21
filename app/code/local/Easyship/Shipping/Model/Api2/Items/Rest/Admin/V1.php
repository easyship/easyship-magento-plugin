<?php

class Easyship_Shipping_Model_Api2_Items_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Items_Rest 
{
      // retrieve
    protected function _retrieve() 
    {

        $orderId    = $this->getRequest()->getParam('id');
        $collection = $this->_getCollectionForSingleRetrieve($orderId);

        $order = $collection->getItemById($orderId);

        if (!$order) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $orderData = $order->getData();
        $addresses = $this->_getAddresses(array($orderId));
        $items     = $this->_getItems(array($orderId));
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
            // $orderData['shipping_addresses'] = $addresses[$orderId];
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

        return $orderData;

    }
}