<?php
/** 
 * Class Easyship_Shipping_Model_Carrier
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.0
 * Author URI: https://www.easyship.com 
*/


class Easyship_Shipping_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface 
{
    protected $_code = 'easyship';

    protected $_configCode = 'easyship_options/ec_shipping/';

    protected $_token;

    protected $_request = null;

    protected $_rawRequest = null;


    /**
     * No method to specify for Mage_Shipping_Model_Carrier_Interface
     *
     * @return null
     */
    public function getAllowedMethods() {
        return null;
    }


    /**
     * helper method to get store configuration
     *
     * @param $code
     * @return mixed
     */
    protected function getStoreConfig( $code ) {
        return Mage::getStoreConfig( $this->_configCode . $code, $this->getStore() );
    }

    /**
     *
     * Check config data if Rate API is active
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return mixed
     */
    protected function getActivate(Mage_Shipping_Model_Rate_Request $request) {
        $id = $request->getStoreId();
        return Mage::getStoreConfig('easyship_options/ec_shipping/store_' . $id . '_isRateEnabled', $this->getStore() );
    }

    /**
     * Collect Rates from this Carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|false|Mage_Core_Model_Abstract
     */

    public function collectRates(Mage_Shipping_Model_Rate_Request $request) 
    {  
        // Configuration setting will be not under carrier scope
        if ( !$this->getConfigFlag('active') || !$this->getActivate($request) )  {
            return false;
        }
        $token_config = $this->_configCode . 'store_' . $request->getStoreId()  . '_token';
        $this->_token = Mage::helper('core')->decrypt(Mage::getStoreConfig($token_config, $this->getStore()));

      //  Mage::log( 'Token: ' . $this->_token, null, 'easyship.log' );

        if ( !$this->_token ) {
            return false;
        }

        // create Easyship Request Body
        $this->_createEasyShipRequest( $request );

        $result = $this->_getQuotes();    

        Mage::log( 'Shipping Rates: ' . var_export( $result, 1), null, 'easyship.log' );    
        return $result;

    }

    /**
     * Construct Request Body for Easyship API
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return $this
     */

    protected function _createEasyShipRequest(Mage_Shipping_Model_Rate_Request $request) {

         $this->_request = $request;

         $r = new Varien_Object();

         if ( $request->getOrigCountry() ) {
            $origCountry = $request->getOrigCountry();
         }
         else {
            $origCountry = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $request->getStoreId() );
         }

         $r->setOriginCountryAlpha2( Mage::getModel('directory/country')->load($origCountry)->getIso2Code() );

         if ( $request->getOrigPostcode() ) {
            $r->setOriginPostalCode( $request->getOrigPostcode() );
         }
         else {
            $r->setOriginPostalCode( Mage::getStoreConfig(
              Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP,
              $request->getStoreId()
            ));
         }

         if ( $request->getDestCountryId() ) {
            $destCountry = $request->getDestCountryId();
         }
         else {
            $destCountry = 'US';
         }

         $r->setDestinationCountryAlpha2( Mage::getModel('directory/country')->load($destCountry)->getIso2Code() );

         if ( $request->getDestPostcode() ) {
            $r->setDestinationPostalCode( $request->getDestPostcode() );
         }

         $r->setOutputCurrency( Mage::app()->getStore()->getCurrentCurrencyCode() );

         $items = array();
         if ( $request->getAllItems() ) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem() ) {
                    continue;
                }

                $_product = Mage::getModel('catalog/product')->load( $item->getProductId() );

                if ( $_product->getHeight() ) {
                  $height = $_product->getHeight();
                }
                else {
                  $height = 1;
                }

                if ( $_product->getWidth() ) {
                  $width = $_product->getWidth();
                }
                else {
                  $width = 1;
                }

                if ( $_product->getLength() ) {
                  $length = $_product->getLength();
                }
                else {
                  $length = 1;
                }

                if ( $_product->getCategory() ) {
                  $category = $_product->getCategory();
                }
                else {
                   $category = 'mobiles';
                }

                for ($i = 0; $i < $item->getQty(); $i++) {
                  $items[] = array(
                      'actual_weight' => $_product->getWeight(),
                    //   'height' => $height, // magento does not have dimension for product
                    //   'width' => $width,
                    //   'length' => $length,
                    //   'category' => $category,
                      'declared_currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                      'declared_customs_value' => (float)$_product->getFinalPrice(),
                      'sku' => $_product->getSku()
                  );
                }
            }
         }
      
         $r->setItems($items);

         $this->_rawRequest = $r;

         Mage::log( '_rawRequest: ' . $this->_rawRequest->toJson(), null, 'easyship.log');
         
         return $this;
    }

    /**
     * Return Quote from Request
     *
     * @return bool|false|Mage_Core_Model_Abstract
     */

    protected function _getQuotes()
    {
        return $this->_doRequest();
    }

    /**
     * Request Rate data from Easyship API
     *
     * @return bool|false|Mage_Core_Model_Abstract
     */

    protected function _doRequest()
    {
        // $dev_env = Mage::getStoreConfig('easyship_options/ec_dev/env');
        // if (isset($dev_env) && $dev_env) {
        //     $url = Mage::getStoreConfig( 'easyship_options/ec_dev/endpoint');
        //     if (!isset($url)) {
        //         Mage::log('endpoint empty', null, 'easyship.log');
        //         throw new Exception('Endpoint has not been set');
        //     }
        // }
        // else {
        //     $url = $this->getConfigData( 'easyship_api_url');
        // }   
        $url = $this->getConfigData( 'easyship_api_url');

        $url = $url . '/rate/v1/magento';
        $client = new Varien_Http_Client($url);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setHeaders(array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->_token
        ));

        $json = $this->_rawRequest->toJson();

        $client->setRawData($json,null);
        $response = $client->request( 'POST');

        if (!$response->isSuccessful()) {
            Mage::log( 'Fail to connect', null, 'easyship.log' );
            Mage::log( var_export( $response, 1), null, 'easyship.log');
            return false;
        }
        
        // decode JSON respond
        $rates = json_decode( $response->getBody(), true );
        Mage::log( 'OK to connect:', null, 'easyship.log' );

        // Get Preferred Rates
        $prefer_rates = $rates['rates']; //$this->_prefer_rates( $rates['rates'] );
        Mage::log( 'Prefer Rates: ' . var_export( $prefer_rates, 1), null, 'easyship.log' );
        
        $result = Mage::getModel('shipping/rate_result');
        foreach ( $prefer_rates as $rate ) {
            $r = Mage::getModel( 'shipping/rate_result_method' );
            $r->setCarrier( $this->_code );
            $r->setCarrierTitle( $this->getConfigData( 'title' ) );
            $r->setMethod( $rate['courier_id'] );
            $r->setMethodTitle( $rate['full_description'] );
            $r->setCost( $rate['total_charge'] );
            $r->setPrice( $rate['total_charge'] );
            $result->append($r);
        }

        return $result;
    }

    /**
     * Set Tracking is available for this carrier
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }


    /**
     * Display shipment tracking Information
     *
     * @param $trackings Tracking Number
     * @return Mage_Shipping_Model_Tracking_Result | bool
     */
    public function getTrackingInfo($trackings) {

        $result = Mage::getModel('shipping/tracking_result');
        $tracking = Mage::getModel('shipping/tracking_result_status');
        $tracking->setCarrier( $this->_code );
        $tracking->setCarrierTitle( $this->getConfigData( 'title' ) );
        $tracking->setTracking($trackings);
        $tracking->setPopup(1);
        $tracking->setUrl("https://www.trackmyshipment.co/shipment-tracking/" . $trackings);
        $result->append($tracking);

        if ($tracks = $result->getAllTrackings()) {
          return $tracks[0];
        }
        return false;
    }
}