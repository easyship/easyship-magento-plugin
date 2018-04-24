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
        return [
            ['value' => 'mobiles', 'label' => __('Mobiles')],
            ['value' => 'tablets', 'label' => __('Tablets')],
            ['value' => 'computers_laptops', 'label' => __('Computers and laptops')],
            ['value' => 'cameras', 'label' => __('Cameras')],
            ['value' => 'accessory_no_battery', 'label' => __('Accessory without battery')],
            ['value' => 'accessory_battery', 'label' => __('Accessory with battery')],
            ['value' => 'health_beauty', 'label' => __('Health & Beauty')],
            ['value' => 'fashion', 'label' => __('Fashion')],
            ['value' => 'watches', 'label' => __('Watches')],
            ['value' => 'home_appliances', 'label' => __('Home appliances')],
            ['value' => 'home_decor', 'label' => __('Home decor')],
            ['value' => 'toys', 'label' => __('Toys')],
            ['value' => 'sport', 'label' => __('Sport')],
            ['value' => 'luggage', 'label' => __('Luggage')],
            ['value' => 'audio_video', 'label' => __('Audio & Video')],
            ['value' => 'documents', 'label' => __('Documents')],
            ['value' => 'jewelry', 'label' => __('Jewelry')],
            ['value' => 'dry_food_supplements', 'label' => __('Dry food supplements')],
            ['value' => 'books_collectionables', 'label' => __('Books')],
            ['value' => 'pet_accessory', 'label' => __('Pet accessory')],
            ['value' => 'gaming', 'label' => __('Gaming')]
        ];
    }
}