<?php
/**
 * Class Easyship_Shipping_Adminhtml_EasyshipController
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.5
 * Author URI: https://www.easyship.com
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
                $storeId = filter_var(Mage::app()->getRequest()->getPost('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);

                // get easyship oauth consumer key and secret

                $request['oauth'] = $this->_getOAuthInfo($storeId);
                $request['user'] = $this->_getUserInfo($storeId);
                // company info
                $request['company'] = $this->_getCompanyInfo($storeId);
                // store info
                $request['store'] = $this->_getStoreInfo($storeId);

                $this->getResponse()->setHeader('Content-type', 'application/json', true);
                $response = $this->_doRequest($storeId, $request);
                $this->getResponse()->setBody(json_encode($response));

            } else {
                Mage::throwException('Method not supported');
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
     *  Retriver OAuth Consumer Information (create new consumer)
     *
     * @return array
     */
    protected function _getOAuthInfo($storeId)
    {
        $response = array();

        /** @var Mage_Oauth_Model_Consumer $consumer */
        $consumer = Mage::getModel('easyship/oauth_consumer')->createOAuthConsumer($storeId);

        // check if using custom admin path
        $route = ((bool)(string)Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH))
            ? Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_CUSTOM_ADMIN_PATH)
            : Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_ADMINHTML_ROUTER_FRONTNAME);

        $response['consumer_key'] = $consumer->getKey();
        $response['consumer_secret'] = $consumer->getSecret();
        $response['request_token_path'] = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $storeId) . 'oauth/initiate';
        $response['request_authorize_path'] = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $storeId) . $route[0] . '/oauth_authorize';
        $response['request_access_token_path'] = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $storeId) . 'oauth/token';

        return $response;
    }

    /**
     * Retrieve Current User Information
     * @param int $store_id Store Id for current store
     * @return array
     */
    protected function _getUserInfo($store_id)
    {
        // get current user information
        $response = array();
        $user = Mage::getSingleton('admin/session')->getUser();

        if (!$user->getEmail()) {
            Mage::throwException('User session is not found');
        }
        $response['email'] = $user->getEmail();
        $response['first_name'] = $user->getFirstname();
        $response['last_name'] = $user->getLastname();
        $response['mobile_phone'] = $user->getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE, $store_id);

        return $response;
    }

    /**
     * Retrieve Compoany Information
     * @param int $storeId Store Id for current store
     * @return array
     */
    protected function _getCompanyInfo($storeId)
    {
        $response = array();
        $response['name'] = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME, $storeId);
        $response['country_code'] = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId);

        return $response;
    }

    /**
     * Retrieve Store Information
     * @param int $storeId Store Id for current store
     * @return array
     */
    protected function _getStoreInfo($storeId)
    {
        $store = Mage::getModel('core/store')->load($storeId);

        if (!$store->getId()) {
            Mage::throwException('store not found');
        }
        $response = array();
        $response['id'] = $storeId;
        $response['name'] = $store->getFrontendName();
        $response['url'] = trim(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $storeId), '/');

        return $response;

    }

    /**
     * start a registration request to Easyship
     *
     * @param int $storeId Store Id for current store
     * @param array $requestBody Request body
     *
     * @return array
     */
    protected function _doRequest($storeId, $requestBody)
    {

        $url = Mage::getStoreConfig('carriers/easyship/easyship_api_url');
        $endpoint = rtrim(trim($url), '/') . '/api/v1/magento/registrations';

        $client = new Varien_Http_Client($endpoint);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setHeaders(array(
            'Content-Type' => 'application/json'
        ));

        $client->setRawData(json_encode($requestBody), null);
        $response = $client->request('POST');

        if (!$response->isSuccessful()) {
            Mage::log('Fail to connect', null, 'easyship.log');
            Mage::throwException('Cannot connect to easyship');
        }

        return json_decode($response->getBody(), true);
    }


    /**
     * Restrict to Admin session
     *
     */
    protected function _isAllowed()
    {
        $adminSession = Mage::getSingleton('admin/session');
        return $adminSession->isAllowed('system/config');
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
                $response = $this->_doRateRequest($store_id, true);
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
                Mage::app()->getCacheInstance()->cleanType('config');
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
     * @param int $store_id Store ID
     * @param bool $enable activate flag
     *
     * @return array
     */
    protected function _doRateRequest($store_id, $enable)
    {
        // use dev
        // $dev_env = Mage::getStoreConfig('easyship_options/ec_dev/env');
        // if (isset($dev_env) && $dev_env) {
        //     $url = Mage::getStoreConfig( 'easyship_options/ec_dev/endpoint');
        //     if (!isset($url)) {
        //         Mage::log('endpoint empty', null, 'easyship.log');
        //         throw new Exception('Endpoint has not been set');
        //     }
        // }
        // else {
        //     $url = Mage::getStoreConfig( 'carriers/easyship/easyship_api_url');
        // }
        $url = Mage::getStoreConfig('carriers/easyship/easyship_api_url');
        $token = Mage::helper('core')->decrypt(Mage::getStoreConfig('easyship_options/ec_shipping/store_' . $store_id . '_token', 0));
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

    /**
     * Reset the plugin
     */
    public function ajaxResetStoreAction()
    {
        $response = array();
        try {
            if ($this->getRequest()->isPost()) {
                $store_id = filter_var(Mage::app()->getRequest()->getPost('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);
                if (!isset($store_id)) {
                    throw new Exception('store id is not set');
                }
                $tokenPath = 'easyship_options/ec_shipping/store_' . $store_id . '_token';
                $enablePath = 'easyship_options/ec_shipping/store_' . $store_id . '_isRateEnabled';
                $activatePath = 'easyship_options/ec_shipping/store_' . $store_id . '_isExtActive';
                Mage::getConfig()->deleteConfig($tokenPath);
                Mage::getConfig()->deleteConfig($enablePath);
                Mage::getConfig()->deleteConfig($activatePath);

                Mage::getModel('easyship/oauth_consumer')->deleteOAuthConsumer($store_id);

                Mage::app()->getCacheInstance()->cleanType('config');
                $this->getResponse()->setHeader('Content-type', 'application/json', true);

                $response['status'] = 'ok';
                Mage::getSingleton('core/session')->addNotice("Easyship has been deactivated successfully, to complete the process please deactivate the store at easyship.com. \n
Go to “Connect” > Find store > click “Delete Store”");
                $this->getResponse()->setBody(json_encode($response));
            } else {
                throw new Exception('Method not supported');
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
}
