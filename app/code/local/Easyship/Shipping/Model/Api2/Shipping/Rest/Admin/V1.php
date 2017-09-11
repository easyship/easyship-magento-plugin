<?php

class Easyship_Shipping_Model_Api2_Shipping_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Shipping_Rest
{
    protected function _retrieve() 
    {
        $page   = $this->getRequest()->getParam('page');
        if (!$page) $page =1;
        $countCollection = Mage::getResourceModel('sales/order_collection');
        $this->_applyFilter($countCollection);

        $collection = $this->_getCollectionForRetrieve();
        $ordersData = array();
        foreach ($collection->getItems() as $order) {
            $ordersData[$order->getId()] = $order->toArray();
        }
       
        $result = array();
        $result['page'] = $page;
        $result['total_count'] = count($countCollection->getItems());
        $result['orders'] = $this->_getOrders(array_keys($ordersData));

        return $result;
    }
   
}