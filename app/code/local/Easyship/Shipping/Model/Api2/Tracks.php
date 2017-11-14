<?php
/** 
 * Class Easyship_Shipping_Model_Api2_Tracks
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.0
 * Autho URI: https://www.easyship.com 
*/

class Easyship_Shipping_Model_Api2_Tracks extends Mage_Api2_Model_Resource 
{

    /**
     * Create track for Shipment
     * 
     * @param string $shipment    IncrementId Increment ID for Shipment
     * @param string $carrier     Carrier Name, should be easyship only
     * @param string $title       Title for Tracking Method
     * @param string $trackNumber Tracking Number provided by Carrier
     */
    protected function _createTracks($shipmentIncrementId, $carrier, $title, $trackNumber) 
    {
        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);

        if (!$shipment->getId()) {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }

        // $carriers = $this->_getCarriers($shipment);

        // if (!isset($carriers[$carrier])) {
        //     $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        // }

        $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($trackNumber)
                    ->setCarrierCode($carrier)
                    ->setTitle($title);

        $shipment->addTrack($track);
        $shipment->getOrder()->setIsInProcess(true);

        try {
            Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();
        }
        catch (Mage_Api2_Exception $e) {
            $this->_critical(self::RESOURCE_UNKNOWN_ERROR);
        }

        return $track;

    }
}