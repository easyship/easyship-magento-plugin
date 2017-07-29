<?php

class Easyship_Shipping_Block_Adminhtml_Config_Generate extends Mage_Adminhtml_Block_System_Config_Form_Field 
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('easyship/system/config/generate.phtml');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    public function getButtonHtml() 
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'generate_button',
                'label'     => $this->helper('easyship')->__('Get Credentials from Easyship'),
                'onclick'   => 'javascript:alert("hello world");'
            ));

        return $button->toHtml();
    }

}