<?php

class Easyship_Shipping_Model_Api2_Items extends Mage_Api2_Model_Resource 
{
      /**#@+
     * Parameters' names in config with special ACL meaning
     */
    
    const PARAM_PAYMENT_METHOD = 'payment';

   

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
     * Retrieve collection instance for single order
     *
     * @param int $orderId Order identifier
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForSingleRetrieve($orderId)
    {
        /** @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection');
     
        return $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), $orderId);
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

        if ($this->_isPaymentMethodAllowed() && $this->_isSubCallAllowed('ec_payment')) {

            $paymnetsFilter = $this->_getSubModel('ec_payment', array())->getFilter();
            foreach ($this->_getPaymentCollection($orderIds)->getItems() as $item) {
                $payments[$item->getParentId()][] = $paymnetsFilter->out($item->toArray());
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
                    $items[$item->getOrderId()][] = $itemsFilter->out($item->toArray());
                }
            }
        }
        return $items;
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

   
    /**
     * Check payment method information is allowed
     *
     * @return bool
     */
    public function _isPaymentMethodAllowed()
    {
        return in_array(self::PARAM_PAYMENT_METHOD, $this->getFilter()->getAllowedAttributes());
    }


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
   