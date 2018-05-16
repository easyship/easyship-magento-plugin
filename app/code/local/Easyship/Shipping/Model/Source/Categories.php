<?php

class Easyship_Shipping_Model_Source_Categories extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function __construct()
    {
        $this->_options = $this->getBaseCategories();
    }

    public function getAllOptions()
    {
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->_options;
    }

    /**
     * Return base easyship categories
     * @see https://developers.easyship.com/reference#request-rates-and-taxes
     * @return array
     */
    protected function getBaseCategories()
    {
        return array(
            array('value' => 'mobiles', 'label' => __('Mobiles')),
            array('value' => 'tablets', 'label' => __('Tablets')),
            array('value' => 'computers_laptops', 'label' => __('Computers and laptops')),
            array('value' => 'cameras', 'label' => __('Cameras')),
            array('value' => 'accessory_no_battery', 'label' => __('Accessory without battery')),
            array('value' => 'accessory_battery', 'label' => __('Accessory with battery')),
            array('value' => 'health_beauty', 'label' => __('Health & Beauty')),
            array('value' => 'fashion', 'label' => __('Fashion')),
            array('value' => 'watches', 'label' => __('Watches')),
            array('value' => 'home_appliances', 'label' => __('Home appliances')),
            array('value' => 'home_decor', 'label' => __('Home decor')),
            array('value' => 'toys', 'label' => __('Toys')),
            array('value' => 'sport', 'label' => __('Sport')),
            array('value' => 'luggage', 'label' => __('Luggage')),
            array('value' => 'audio_video', 'label' => __('Audio & Video')),
            array('value' => 'documents', 'label' => __('Documents')),
            array('value' => 'jewelry', 'label' => __('Jewelry')),
            array('value' => 'dry_food_supplements', 'label' => __('Dry food supplements')),
            array('value' => 'books_collectionables', 'label' => __('Books')),
            array('value' => 'pet_accessory', 'label' => __('Pet accessory')),
            array('value' => 'gaming', 'label' => __('Gaming'))
        );
    }
}