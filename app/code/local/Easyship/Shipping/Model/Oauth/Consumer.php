<?php

class Easyship_Shipping_Model_Oauth_Consumer extends Mage_Core_Helper_Abstract
{
    const EASYSHIP_CONSUMER_PREFIX = 'easyship_';

    /**
     * Create OAuth Consumer by store id
     * @param $storeId
     * @return bool|Mage_Oauth_Model_Consumer
     */
    public function createOAuthConsumer($storeId)
    {
        if (empty($storeId)) {
            return false;
        }

        $storeId = intval($storeId);

        $consumerName = self::EASYSHIP_CONSUMER_PREFIX . $storeId;
        $consumerDeleted = $this->isExistConsumer($consumerName);

        if (is_object($consumerDeleted)) {
            $this->deleteOAuthTokensByConsumer($consumerDeleted->getId());
            $consumerDeleted->delete();
            unset($consumerDeleted);
        }

        /**
         * @var Mage_Oauth_Model_Consumer $consumer
         */
        $consumer = Mage::getModel('oauth/consumer');

        /** @var $helper Mage_Oauth_Helper_Data */
        $helper = Mage::helper('oauth');

        $consumer->setData([
            'name' => $consumerName,
            'key' => $helper->generateConsumerKey(),
            'secret' => $helper->generateConsumerSecret(),
        ]);

        $consumer->save();

        return $consumer;
    }

    /**
     * Delete OAuth Consumer by store id
     * @param $storeId
     * @return bool
     */
    public function deleteOAuthConsumer($storeId)
    {
        $consumerName = self::EASYSHIP_CONSUMER_PREFIX . $storeId;
        $consumer = $this->isExistConsumer($consumerName);

        if (!is_object($consumer)) {
            return false;
        }

        $this->deleteOAuthTokensByConsumer($consumer->getId());
        $consumer->delete();

        return true;
    }

    /**
     * @param string $name
     * @return bool|Mage_Oauth_Model_Consumer
     */
    protected function isExistConsumer($name)
    {
        /**
         * @var Mage_Oauth_Model_Consumer $consumer
         */
        $consumer = Mage::getModel('oauth/consumer')
            ->getCollection()
            ->addFieldToFilter('name', $name)
            ->getFirstItem();

        if (empty($consumer) || !$consumer->hasName() || ($consumer->getName() != $name)) {
            return false;
        }

        return $consumer;
    }

    /**
     * Delete all OAuth tokens by consumer
     * @param $consumerId
     */
    protected function deleteOAuthTokensByConsumer($consumerId)
    {
        /**
         * @var Mage_Oauth_Model_Resource_Token_Collection $collection
         */
        $collection = Mage::getModel('oauth/token')
            ->getCollection()
            ->addFieldToFilter('consumer_id', $consumerId);

        if ($collection->getSize() > 0) {
            $collection->clear();
        }
    }
}