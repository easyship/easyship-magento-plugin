<?php
/**
 * Class Easyship_Shipping_Model_Api2_Shipment
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.3
 * Author URI: https://www.easyship.com
*/

class Easyship_Shipping_Model_Api2_Shipment extends Mage_Api2_Model_Resource
{

    protected function _prepareItemQtyData($data)
    {
        $_data = array();
        foreach ($data as $item) {
            if (isset($item['order_item_id']) && isset($item['qty'])) {
                $_data[$item['order_item_id']] = $item['qty'];
            }
        }
        return $_data;
    }

    protected function _createShipment($orderIncrementId, $itemsQty = array(), $comment = null, $email = null, $includeComment = false)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $itemsQty = $this->_prepareItemQtyData($itemsQty);

        $shipment = $order->prepareShipment($itemsQty);
        if ($shipment) {
            $shipment->register();
            $shipment->addComment($comment, $email && $includeComment);
            if ($email) {
                $shipment->setEmailSent(true);
            }
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                $shipment->sendEmail($email, ($includeComment ? $comment : ''));
            }
            catch (Mage_Core_Exception $e) {
                $this->_crtitcal('Data Invalid', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            return $shipment->getId();
        }
        return null;
    }

    protected function _getCollectionForSingleRetrieve($shipmentId)
    {
        $collection = Mage::getResourceModel('sales/order_shipment_collection');

        return $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), $shipmentId);
    }

    protected function _getItems(array $shipmentIds)
    {
        $items = array();
        if ($this->_isSubCallAllowed('ec_shipment_item')) {
            $itemsFilter = $this->_getSubModel('ec_shipment_item', array())->getFilter();

            if ($itemsFilter->getAllowedAttributes()) {
                $collection = Mage::getResourceModel('sales/order_shipment_item_collection');

                $collection->addAttributeToFilter('parent_id', $shipmentIds);

                foreach ($collection->getItems() as $item) {
                    $items[] = $itemsFilter->out($item->toArray());
                }
            }
        }
        return $items;
    }

    protected function _getComments(array $shipmendIds)
    {
        $comments = array();
        if ($this->_isSubCallAllowed('ec_shipment_comment')) {
            $commentsFilter = $this->_getSubModel('ec_shipment_comment', array())->getFilter();

            if ($commentsFilter->getAllowedAttributes()) {
                $collection = Mage::getResourceModel('sales/order_shipment_comment_collection');

                $collection->addAttributeToFilter('parent_id', $shipmentIds);

                foreach ($collection->getItems() as $item) {
                    $comments[] = $commentsFilter->out($item->toArray());
                }
            }
        }
        return $comments;
    }

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
