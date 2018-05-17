<?php
/**
 * Class Easyship_Shipping_Model_Api2_Shipping_Rest_Admin_V1
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.3
 * Author URI: https://www.easyship.com
 */

class Easyship_Shipping_Model_Api2_Shipping_Rest_Admin_V1 extends Easyship_Shipping_Model_Api2_Shipping_Rest
{
    const BASE_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Handle GET Request
     */
    protected function _retrieve()
    {
        $page = $this->getRequest()->getParam('page');
        if (!$page) $page = 1;

        $collection = $this->_getCollectionForRetrieve();

        $params = $this->getRequest()->getParams();

        if ($this->_validateDateParams($params)) {
            $collection->addFieldToFilter(
                'updated_at',
                array(
                    'from' => $params['created_at_from'],
                    'to' => $params['created_at_to']
                )
            );
        }

        $ordersData = array();
        foreach ($collection->getItems() as $order) {
            $ordersData[$order->getId()] = $order->toArray();
        }

        $result = array();
        $result['page'] = $page;
        $result['total_count'] = count($collection->getItems());
        $result['orders'] = $this->_getOrders(array_keys($ordersData));

        return $result;
    }

    /**
     * @param $params
     * @return bool
     */
    protected function _validateDateParams($params)
    {
        if (empty($params) || !is_array($params)) {
            return false;
        }

        if (empty($params['created_at_from']) || empty($params['created_at_to'])) {
            return false;
        }

        if ($this->_validateDate($params['created_at_from']) || !$this->_validateDate($params['created_at_to'])) {
            return true;
        } else {
            $this->_critical('Invalid date format, please give date to this type -  Y-m-d H:i:s ', 400);
        }

        return false;
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

}
