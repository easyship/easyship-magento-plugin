<?php

class Easyship_Shipping_Model_Api2_Shipping_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Shipping_Rest
{
    protected function _retrieve() 
    {
        $page   = $this->getRequest()->getParam('page');
        if (!$page) $page =1;
        $collection = $this->_getCollectionForRetrieve();
        $ordersData = array();
        foreach ($collection->getItems() as $order) {
            $ordersData[$order->getId()] = $order->toArray();
        }
        $result = array();
        $result['page'] = $page;
        $result['total_count'] = count($ordersData);
        $result['orders'] = $this->_getOrders(array_keys($ordersData));

        return $result;
    }
   
}