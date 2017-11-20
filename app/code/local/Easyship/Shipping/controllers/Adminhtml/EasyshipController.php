<?php
/**
 * Class Easyship_Shipping_Adminhtml_EasyshipController
 * Author: Easyship
 * Developer: Sunny Cheung, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.0
 * Autho URI: https://www.easyship.com
*/

class Easyship_Shipping_Adminhtml_EasyshipController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Start Easyship Registration Flow
     */
    public function ajaxRegisterAction()
    {
        $response = array();
        try {
            if ($this->getRequest()->isPost()) {
                $request = array();
                $store_id = filter_var(Mage::app()->getRequest()->getPost('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);

                // get easyship oauth consumer key and secret

                $request['oauth'] = $this->_getOAuthInfo();
                $request['user'] = $this->_getUserInfo($store_id);
                // company info
                $request['company'] = $this->_getCompanyInfo($store_id);
                // store info
                $request['store'] = $this->_getStoreInfo($store_id);

                $this->getResponse()->setHeader('Content-type', 'application/json', true);
                $response =  $this->_doRequest($store_id, $request);
                $this->getResponse()->setBody(json_encode($response));


            }
            else {
                throw new Exception('Method not supported');
            }

        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'easyship.log');
            $response['error'] = $e->getMessage();
            $this->getResponse()->clearHeaders()->setHeader('HTTP/1.1', '404 Not Found');
            $this->getResponse()->setHeader('Status', 404);

            $this->getResponse()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode($response));
        }
    }

    /**
     *  Retriver OAuth Consumer Information
     *
     *  @return array
     */
    protected function _getOAuthInfo()
    {
        $response = array();
        $collection = Mage::getModel('oauth/consumer')->getCollection();

        foreach ($collection as $consumer) {
            if ($consumer->getName() == 'easyship') {

                $response['consumer_key'] = $consumer->getKey();
                $response['consumer_secret'] = $consumer->getSecret();
                return $response;
            }
        }

        throw new Exception('Easyship consumer not found');
    }

    /**
     * Retrieve Current User Information
     * @param int $store_id   Store Id for current store
     * @return array
     */
    protected function _getUserInfo($store_id)
    {
        // get current user information
        $response = array();
        $user = Mage::getSingleton('admin/session')->getUser();

        if (!$user->getEmail()) {
            throw new Exception('User session is not found');
        }
        $response['email'] = $user->getEmail();
        $response['first_name'] = $user->getFirstname();
        $response['last_name'] = $user->getLastname();
        $response['mobile_phone'] = $user->getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE, $store_id);

        return $response;
    }

    /**
     * Retrieve Compoany Information
     * @param int $store_id   Store Id for current store
     * @return array
     */
    protected function _getCompanyInfo($store_id)
    {
        $response = array();
        $response['name'] = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME, $store_id);
        $response['country_code'] = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $store_id );

        return $response;
    }

    /**
     * Retrieve Store Information
     * @param int $store_id   Store Id for current store
     * @return array
     */
    protected function _getStoreInfo($store_id)
    {
        $store = Mage::getModel('core/store')->load($store_id);

        if (!$store->getId()) {
            throw new Exception('store not found');
        }
        $response = array();
        $response['id'] = $store_id;
        $response['name'] = $store->getFrontendName();
        $response['url'] = trim(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $store_id), '/');

        return $response;

    }


    /**
     * start a registration request to Easyship
     *
     * @param int $store_id  Store Id for current store
     * @param array $requestBody  Request body
     *
     * @return array
     */
    protected function _doRequest($store_id, $requestBody)
    {
        $url = Mage::getStoreConfig( 'carriers/easyship/easyship_api_url');
        $endpoint = rtrim(trim($url), '/') . '/api/v1/magento/registrations';

        $client = new Varien_Http_Client($endpoint);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setHeaders(array(
            'Content-Type' => 'application/json'
        ));

        $client->setRawData(json_encode($requestBody), null);
        $response = $client->request('POST');

        if (!$response->isSuccessful()) {
            Mage::log( 'Fail to connect', null, 'easyship.log' );
            throw new Exception( 'Cannot connect to easyship' );
        }

        return json_decode( $response->getBody(), true );
    }


    /**
     * Restrict to Admin session
     *
     */
    protected function _isAllowed()
    {
        $adminSession = Mage::getSingleton( 'admin/session' );
        return $adminSession->isAllowed( 'system/config' );
    }

    /**
     * Activate Easyship RATE API
     *
     */
    public function ajaxActivateAction()
    {
        $response = array();
        try {
            if ($this->getRequest()->isPost()) {

                $store_id = filter_var(Mage::app()->getRequest()->getPost('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);
                $enablePath = 'easyship_options/ec_shipping/store_' . $store_id . '_isRateEnabled';
                Mage::getConfig()->saveConfig($enablePath, '1', 'default', 0);
                $response = $this->_doRateRequest($store_id,true);
                $this->getResponse()->setHeader('Content-type', 'application/json', true);
                $response['status'] = 'ok';
                $this->getResponse()->setBody(json_encode($response));
            }
        }
        catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'easyship.log');
            $response['error'] = $e->getMessage();
            $this->getResponse()->clearHeaders()->setHeader('HTTP/1.1', '400 Bad Request');
            $this->getResponse()->setHeader('Status', 400);

            $this->getResponse()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode($response));
        }
    }

    /**
     * Deactivate Easyship RATE API
     *
     */
    public function ajaxDeactivateAction()
    {
        $response = array();
        try {
            if ($this->getRequest()->isPost()) {
                $store_id = filter_var(Mage::app()->getRequest()->getPost('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);
                $enablePath = 'easyship_options/ec_shipping/store_' . $store_id . '_isRateEnabled';
                Mage::getConfig()->saveConfig($enablePath, '0', 'default', 0);
                $response = $this->_doRateRequest($store_id, false);
                $this->getResponse()->setHeader('Content-type', 'application/json', true);
                $response['status'] = 'ok';
                $this->getResponse()->setBody(json_encode($response));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'easyship.log');
            $response['error'] = $e->getMessage();
            $this->getResponse()->clearHeaders()->setHeader('HTTP/1.1', '400 Bad Request');
            $this->getResponse()->setHeader('Status', 400);

            $this->getResponse()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode($response));
        }
    }


    /**
     * Flag to Easyship when User activate/deactivate Rate API
     *
     * @param int $store_id  Store ID
     * @param bool $enable   activate flag
     *
     * @return array
     */
    protected function _doRateRequest($store_id, $enable)
    {

        $url = Mage::getStoreConfig( 'carriers/easyship/easyship_api_url');
        $token = Mage::helper('core')->decrypt(Mage::getStoreConfig('easyship_options/ec_shipping/store_' . $store_id  . '_token'));
        $endpoint = rtrim(trim($url), '/') . '/store/v1/stores';
        $requestBody = array();
        $requestBody['store'] = array();
        $requestBody['store']['is_rates_enabled'] = $enable;

        $client = new Zend_Http_Client($endpoint);
        $client->setMethod(Varien_Http_Client::PUT);
        $client->setHeaders(array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ));

        $client->setRawData(json_encode($requestBody), null);
        $response = $client->request('PUT');

        if (isset($response)) {
            if (!$response->isSuccessful()) {
                Mage::log('Fail to set ', null, 'easyship.log');
              //  throw new Exception('Cannot connect to easyship');
            }
        }
       return array();
    }
}
