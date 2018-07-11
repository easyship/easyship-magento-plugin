<?php
/**
 * Class Easyship_Shipping_Model_Api2_Shipping
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.5
 * Author URI: https://www.easyship.com
 */

class Easyship_Shipping_Model_Api2_Shipping extends Mage_Api2_Model_Resource
{
    /**
     * Retrieve collection instance for orders list
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /** @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection');

        $this->_applyCollectionModifiers($collection);

        return $collection;
    }

    /**
     * Retrieve a list or orders' addresses in a form of [order ID => array of addresses, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getAddresses(array $orderIds)
    {
        $addresses = array();

        if ($this->_isSubCallAllowed('ec_address')) {
            /** @var $addressesFilter Mage_Api2_Model_Acl_Filter */
            $addressesFilter = $this->_getSubModel('ec_address', array())->getFilter();
            // do addresses request if at least one attribute allowed
            if ($addressesFilter->getAllowedAttributes()) {
                /* @var $collection Mage_Sales_Model_Resource_Order_Address_Collection */
                $collection = Mage::getResourceModel('sales/order_address_collection');

                $collection->addAttributeToFilter('parent_id', $orderIds);

                foreach ($collection->getItems() as $item) {
                    $addresses[$item->getParentId()][] = $addressesFilter->out($item->toArray());
                }
            }
        }
        return $addresses;
    }

    /**
     * Retrieve payment of [order ID => array of payment, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getPayment(array $orderIds)
    {
        $payments = array();

        if ($this->_isSubCallAllowed('ec_payment')) {

            $paymnetsFilter = $this->_getSubModel('ec_payment', array())->getFilter();
            foreach ($this->_getPaymentCollection($orderIds)->getItems() as $item) {
                $payments[$item->getParentId()] = $paymnetsFilter->out($item->toArray());
            }
        }
        return $payments;
    }

    /**
     * Prepare and return payment collection
     *
     * @param array $orderIds Orders' identifiers
     * @return Mage_Sales_Model_Resource_Order_Payment_Collection|Object
     */
    protected function _getPaymentCollection(array $orderIds)
    {
        $collection = Mage::getResourceModel('sales/order_payment_collection');
        $collection->setOrderFilter($orderIds);

        return $collection;
    }

    /**
     * Retrieve a list or orders' items in a form of [order ID => array of items, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getItems(array $orderIds)
    {
        $items = array();

        if ($this->_isSubCallAllowed('ec_items')) {

            $itemsFilter = $this->_getSubModel('ec_items', array())->getFilter();

            if ($itemsFilter->getAllowedAttributes()) {
                /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
                $collection = Mage::getResourceModel('sales/order_item_collection');

                $collection->addAttributeToFilter('order_id', $orderIds);

                foreach ($collection->getItems() as $item) {
                    $dimension_data = $this->getDimension($item->getProduct());
                    $data = array_merge($itemsFilter->out($item->toArray()), $dimension_data);

                    $items[$item->getOrderId()][] = $data;
                }
            }
        }

        return $items;
    }

    protected function getDimension($product)
    {
        /** @var Easyship_Shipping_Helper_Data $helper */
        $helper = Mage::helper('easyship');
        $data = [
            'easyship_height' => $helper->getEasyshipHeight($product),
            'easyship_width' => $helper->getEasyshipWidth($product),
            'easyship_length' => $helper->getEasyshipLength($product),
            'weight_unit' => 'kg'
        ];

        return $data;
    }

    /**
     *  Retrive order status in a form of [order ID => status history, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getStatusHistory(array $orderIds)
    {
        $status_history = array();

        if ($this->_isSubCallAllowed('ec_status_history')) {
            $statusFilter = $this->_getSubModel('ec_status_history', array())->getFilter();

            if ($statusFilter->getAllowedAttributes()) {
                $collection = Mage::getResourceModel('sales/order_status_history_collection');

                $collection->addAttributeToFilter('parent_id', $orderIds);

                foreach ($collection->getItems() as $item) {
                    $status_history[$item->getParentId()][] = $statusFilter->out($item->toArray());
                }
            }
        }
        return $status_history;
    }


    protected function _getOrders(array $orderIds)
    {
        $orders = array();
        if (count($orderIds) == 0) {
            return $orders;
        }
        $items = $this->_getItems($orderIds);
        $addresses = $this->_getAddresses($orderIds);
        $payments = $this->_getPayment($orderIds);
        $status = $this->_getStatusHistory($orderIds);
        $shipments = $this->_getShipment($orderIds);

        if ($this->_isSubCallAllowed('ec_order')) {
            $orderFilter = $this->_getSubModel('ec_order', array())->getFilter();

            if ($orderFilter->getAllowedAttributes()) {
                $collection = Mage::getResourceModel('sales/order_collection');

                $collection->addAttributeToFilter('entity_id', $orderIds);

                foreach ($collection->getItems() as $order) {
                    $orderId = $order->getId();
                    $_order = $orderFilter->out($order->toArray());
                    $_order['order_id'] = $orderId;
                    $addressesCount = count($addresses[$orderId]);

                    for ($i = 0; $i < $addressesCount; $i++) {
                        $address = $addresses[$orderId][$i];
                        if ($address['address_type'] == 'billing') {
                            $_order['billing_firstname'] = $address['firstname'];
                            $_order['billing_lastname'] = $address['lastname'];
                            if ($address['name']) {
                                $_order['billing_name'] = $address['name'];
                            }
                        } else if ($address['address_type'] == 'shipping') {
                            $_order['shipping_firstname'] = $address['firstname'];
                            $_order['shipping_lastname'] = $address['lastname'];
                            $_order['shipping_address'] = $address;
                            if ($address['name']) {
                                $_order['shipping_name'] = $address['name'];
                            }
                        }
                    }
                    $_order['items'] = $items[$orderId];
                    $_order['payment'] = $payments[$orderId];
                    $_order['status_history'] = $status[$orderId];
                    $_order['shipments'] = $shipments[$orderId] ? $shipments[$orderId] : array();
                    $orders[] = $_order;
                }
            }
        }
        return $orders;
    }

    /**
     *  Retrieve Shipment Status
     *
     * @param array $orderIds
     * @return array
     */
    protected function _getShipment(array $orderIds)
    {
        $shipments = array();

        if ($this->_isSubCallAllowed('ec_shipments')) {
            $statusFilter = $this->_getSubModel('ec_shipments', array())->getFilter();

            if ($statusFilter->getAllowedAttributes()) {
                $collection = Mage::getResourceModel('sales/order_shipment_collection');
                $collection->addAttributeToFilter('order_id', $orderIds);

                foreach ($collection->getItems() as $item) {

                    $order = Mage::getModel('sales/order')->load($item->getOrderId());
                    $filteredResult = $statusFilter->out($item->toArray());
                    $filteredResult['order_increment_id'] = $order->getIncrementId();
                    $filteredResult['tracks'] = $this->_getTracks(array($item->getId()));
                    $shipments[$item->getOrderId()][] = $filteredResult;

                }
            }

        }
        return $shipments;
    }

    /**
     * Retrieve track for shipment
     *
     * @param array $shipmentIds
     * @return array
     */

    protected function _getTracks(array $shipmentIds)
    {
        $tracks = array();
        if ($this->_isSubCallAllowed('ec_shipments_track')) {
            $tracksFilter = $this->_getSubModel('ec_shipments_track', array())->getFilter();

            if ($tracksFilter->getAllowedAttributes()) {
                $collection = Mage::getResourceModel('sales/order_shipment_track_collection');

                $collection->addAttributeToFilter('parent_id', $shipmentIds);

                foreach ($collection->getItems() as $item) {
                    $tracks[] = $tracksFilter->out($item->toArray());
                }
            }
        }
        return $tracks;
    }


}
