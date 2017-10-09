<?php

class Easyship_Shipping_Model_Api2_Tracks extends Mage_Api2_Model_Resource 
{
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