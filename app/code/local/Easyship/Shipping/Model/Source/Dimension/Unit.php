<?php

class Easyship_Shipping_Model_Source_Dimension_Unit extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function __construct()
    {
        $this->_options = $this->getBaseDimensionUnit();
    }

    public function getAllOptions()
    {
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->_options;
    }

    protected function getBaseDimensionUnit()
    {
        return array(
            array('value' => 'cm', 'label' => __('cm')),
            array('value' => 'in', 'label' => __('in')),
        );
    }
}