<?php
/**
 * Class Easyship_Shipping_Helper_Data
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 1.0.0
 * Author URI: https://www.easyship.com
*/

class Easyship_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get store id
     * @return mixed
     */
    protected function getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    /**
     * @param $item
     * @return float
     */
    public function getWeightConvert($item)
    {
        $weight = $item->getWeight();
        $unit = $this->getWeightUnit();

        switch ($unit) {
            case 'lbs':
                return $weight * 0.45359237;
        }

        return $weight;
    }

    /**
     * Get easyship height
     * @param $item
     * @return int
     */
    public function getEasyshipHeight($item)
    {
        $productHeight = $item->getEasyshipHeight();
        if (!empty($productHeight)) {
            return (int)$productHeight;
        }

        $base_height = Mage::getStoreConfig('easyship_options/general/base_height', $this->getStoreId());

        if (empty($base_height)) {
            return 0;
        }

        return (int)$base_height;
    }

    public function getEasyshipHeightConvert($item)
    {
        $height = $this->getEasyshipHeight($item);
        $unit = $this->getDimensionUnit();

        switch ($unit) {
            case 'in':
                return $this->convertInToCm($height);
        }

        return $height;
    }

    /**
     * Get easyship width
     * @param $item
     * @return int
     */
    public function getEasyshipWidth($item)
    {
        $productWidth = $item->getEasyshipWidth();
        if (!empty($productWidth)) {
            return (int)$productWidth;
        }

        $base_width = Mage::getStoreConfig('easyship_options/general/base_width', $this->getStoreId());

        if (empty($base_width)) {
            return 0;
        }

        return (int)$base_width;
    }

    public function getEasyshipWidthConvert($item)
    {
        $width = $this->getEasyshipWidth($item);
        $unit = $this->getDimensionUnit();

        switch ($unit) {
            case 'in':
                return $this->convertInToCm($width);
        }

        return $width;
    }

    /**
     * Get easyship length
     * @param $item
     * @return int
     */
    public function getEasyshipLength($item)
    {
        $productLength = $item->getEasyshipLength();
        if (!empty($productLength)) {
            return (int)$productLength;
        }

        $base_length = Mage::getStoreConfig('easyship_options/general/base_length', $this->getStoreId());

        if (empty($base_length)) {
            return 0;
        }

        return (int)$base_length;
    }

    public function getEasyshipLengthConvert($item)
    {
        $length = $this->getEasyshipLength($item);
        $unit = $this->getDimensionUnit();

        switch ($unit) {
            case 'in':
                return $this->convertInToCm($length);
        }

        return $length;
    }

    /**
     * @return string
     */
    public function getDimensionUnit()
    {
        $dimension_unit = Mage::getStoreConfig('easyship_options/general/dimension_unit', $this->getStoreId());

        return $dimension_unit;
    }

    /**
     * @return string
     */
    public function getWeightUnit()
    {
        $dimension_unit = Mage::getStoreConfig('easyship_options/general/weight_unit', $this->getStoreId());

        return $dimension_unit;
    }

    protected function convertInToCm($value)
    {
        return $value * 2.54;
    }
}
