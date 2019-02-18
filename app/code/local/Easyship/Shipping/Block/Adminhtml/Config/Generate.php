<?php
/**
 * Class Easyship_Shipping_Block_Adminhtml_Config_Generate
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 1.0.2
 * Author URI: https://www.easyship.com
*/

class Easyship_Shipping_Block_Adminhtml_Config_Generate extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('easyship/system/config/generate.phtml');
    }

    /**
     * Prepare Layout for the field
     *
     * @return Mage_Adminhtml_Block_System_Config_Form_Field
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('easyship/system/config/generate.phtml');
        }
        return $this;
    }

    /**
     * Render function for the Field
     *
     * @param Vairen_Data_Form_element_Abstract
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $id = $element->getHtmlId();

        $html = '<td><div id="easyship">';

        $html .= $this->_getElementHtml($element);

        $html .= '</div></td>';

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';

        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td>';

        return $this->_decorateRowHtml($element, $html);
    }


    /**
     * populate configuration data to HTML
     *
     * @param Vairen_Data_From_Element_Abstract
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        $id = $element->getStoreid();
        $isActived = Mage::getStoreConfig('easyship_options/ec_shipping/store_' . $id . '_isExtActive', 0);
        $isEnabled = Mage::getStoreConfig('easyship_options/ec_shipping/store_' . $id . '_isRateEnabled', 0);
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/easyship/ajaxregister');
        $enableRateUrl = Mage::helper('adminhtml')->getUrl('adminhtml/easyship/ajaxactivate');
        $disableRateUrl = Mage::helper('adminhtml')->geturl('adminhtml/easyship/ajaxdeactivate');
        $resetUrl = Mage::helper('adminhtml')->getUrl('adminhtml/easyship/ajaxresetstore');
        $this->addData(
            array(
                'store' => $element->getLabel(),
                'storeid' => $id,
                'enabled' => $isEnabled,
                'actived' => $isActived,
                'storeurl' => $url,
                'acturl' => $enableRateUrl,
                'deacturl' => $disableRateUrl,
                'resetstoreurl' => $resetUrl

            )
        );

        return $this->_toHtml();
    }


}
