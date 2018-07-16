<?php

class Easyship_Shipping_Model_Source_Weight_Unit extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function __construct()
    {
        $this->_options = $this->getBaseWidthUnit();
    }

    public function getAllOptions()
    {
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->_options;
    }

    protected function getBaseWidthUnit()
    {
        return array(
            array('value' => 'kg', 'label' => __('kg')),
            array('value' => 'lbs', 'label' => __('lbs')),
        );
    }
}