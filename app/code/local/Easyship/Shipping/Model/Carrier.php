<?php

class Easyship_Shipping_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface 
{
    protected $_code = 'easyship';

    protected $_configCode = 'easyship_options/es_shipping/';

    protected $_token = 'e1ed01236eb0112f264bedf1f2a16206f4c01cdf0539c83241aa05a57a91b1c9';

    protected $_request = null;

    protected $_rawRequest = null;

    public function getAllowedMethods() {
        return array(
            'easyship' => $this->getConfigData('allowed_methods')
        );
    }
    
    protected function getStoreConfig( $code ) {
        return Mage::getStoreConfig( $this->_configCode . $code, $this->getStore() );
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request) 
    {  
        // Configuration setting will be not under carrier scope
        if ( !$this->getConfigFlag('active') || !$this->getStoreConfig( 'active' ) )  {
            return false;
        }

        //$this->_token =  Mage::helper('core')->decrypt( $this->getConfigData( 'token' ) );
        Mage::log( 'Token: ' . $this->_token, null, 'easyship.log' );
        if ( !$this->_token ) {
            return false;
        }

        // create Easyship Request Body
        $this->_createEasyShipRequest( $request );

        $result = $this->_getQuotes();    

        Mage::log( 'Shipping Rates: ' . var_export( $result, 1), null, 'easyship.log' );    
        return $result;

    }

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
                      'actual_weight' => 1.2,
                      'height' => $height, // magento does not have dimension for product
                      'width' => $width,
                      'length' => $length,
                      'category' => $category,
                      'declared_currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                      'declared_customs_value' => (float)$_product->getFinalPrice()
                  );
                }
            }
         }
      
         $r->setItems($items);

         $this->_rawRequest = $r;

         Mage::log( '_rawRequest: ' . $this->_rawRequest->toJson(), null, 'easyship.log');
         
         return $this;
    }

    protected function _getQuotes()
    {
        return $this->_doRequest();
    }

    protected function _doRequest()
    {

        $url = $this->getConfigData( 'easyship_stage_api_url' );

        if ( !$url ) {
            $url = 'https://api-staging.easyship.com/rate/v1/rates';
        }
        Mage::log( 'Request URL: ' . $url, null, "easyship.log" );
   

        $client = new Varien_Http_Client('https://api-staging.easyship.com/rate/v1/rates');
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
            return false;
        }
        
        // decode JSON respond
        $rates = json_decode( $response->getBody(), true );
        Mage::log( 'OK to connect:', null, 'easyship.log' );

        // Get Preferred Rates
        $prefer_rates = $this->_prefer_rates( $rates['rates'] );
        Mage::log( 'Prefer Rates: ' . var_export( $prefer_rates, 1), null, 'easyship.log' );
        
        $result = Mage::getModel('shipping/rate_result');
        foreach ( $prefer_rates as $rate ) {
            $r = Mage::getModel( 'shipping/rate_result_method' );
            $method_title = $rate['courier_name'] . ' (' . $rate['min_delivery_time'] . '-' . $rate['max_delivery_time'] . ' days)';
            $r->setCarrier( $this->_code );
            $r->setCarrierTitle( $this->getConfigData( 'title' ) );
            $r->setMethod( $rate['courier_id'] );
            $r->setMethodTitle( $method_title );
            $r->setCost( $rate['total_charge'] );
            $r->setPrice( $rate['total_charge'] );
            $result->append($r);
        }

        return $result;
    }



    protected function _prefer_rates( $rates ) {

        $prefer_rates = array();
        $lowest = 0;
        $index = 0;

        if ( empty( $rates ) ) {
            return $prefer_rate;
        }

        foreach ($rates as $rate) {
            if ( $rates[$lowest]['total_charge'] > $rate['total_charge'] ) {
                $lowest = $index;
            }
            if ( $rate["delivery_time_rank"] == 1 ) {
                $prefer_rates[$rate['courier_id']] = $rate;
            }
            if ( $rate["value_for_money_rank"] == 1 ) {
                $prefer_rates[$rate['courier_id']] = $rate;
            }
            $index++;
        }

        $prefer_rates[$rates[$lowest]['courier_id']] = $rates[$lowest];
        return $prefer_rates;
    }
}