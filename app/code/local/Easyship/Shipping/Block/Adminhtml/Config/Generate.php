<?php

class Easyship_Shipping_Block_Adminhtml_Config_Generate extends Mage_Adminhtml_Block_System_Config_Form_Field 
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('easyship/system/config/generate.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout(); // TODO: Change the autogenerated stub
        if (!$this->getTemplate()) {
            $this->setTemplate('easyship/system/config/generate.phtml');
        }
        return $this;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $id = $element->getHtmlId();
        // $html = '<td class="label"><label for="'.$id.'"><strong>Store Name: </strong>'.$element->getLabel().'</label></td>';

        // //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        // $isMultiple = $element->getExtType()==='multiple';

        // // replace [value] with [inherit]
        // $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        // $options = $element->getValues();

        // $addInheritCheckbox = false;
        // if ($element->getCanUseWebsiteValue()) {
        //     $addInheritCheckbox = true;
        //     $checkboxLabel = $this->__('Use Website');
        // }
        // elseif ($element->getCanUseDefaultValue()) {
        //     $addInheritCheckbox = true;
        //     $checkboxLabel = $this->__('Use Default');
        // }

        // if ($addInheritCheckbox) {
        //     $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
        //     if ($inherit) {
        //         $element->setDisabled(true);
        //     }
        // }

        // if ($element->getTooltip()) {
        //     $html .= '<td class="value with-tooltip">';
        //     $html .= $this->_getElementHtml($element);
        //     $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
        // } else {
        //     $html .= '<td class="value">';
        //     $html .= $this->_getElementHtml($element);
        // };
        // if ($element->getComment()) {
        //     $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        // }
        // $html.= '</td>';

        $html = '<td class="label" colspan="2"><div id="easyship">';

        $html .= $this->_getElementHtml($element);

        $html .= '</div></td>';
        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif (isset($v['value'])) {
                        if ($v['value'] == $defText) {
                            $defTextArr[] = $v['label'];
                            break;
                        }
                    } elseif (!is_array($v)) {
                        if ($k == $defText) {
                            $defTextArr[] = $v;
                            break;
                        }
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html.= '<td class="use-default">';
            $html.= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html.= '</td>';
        }

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

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        $id = $element->getStoreid();
        $is_actived = Mage::app()->getStore($id)->getConfig('easyship_options/ec_shipping/store_' . $id . '_isExtActive');
        $is_enabled = Mage::app()->getStore($id)->getConfig('easyship_options/ec_shipping/store_' . $id . '_isRateEnabled');
        $url = Mage::helper( 'adminhtml')->getUrl('adminhtml/easyship/ajaxregister');
        $enable_rate_url = Mage::helper('adminhtml')->getUrl('adminhtml/easyship/ajaxactivate');
        $disable_rate_url = Mage::helper('adminhtml')->geturl('adminhtml/easyship/ajaxdeactivate');
        $this->addData(
            array(
                'store' => $element->getLabel(),
                'storeid' => $id,
                'enabled' => $is_enabled,
                'actived' => $is_actived,
                'storeurl' => $url,
                'acturl' => $enable_rate_url,
                'deacturl' => $disable_rate_url
            )
        );

        return $this->_toHtml();
    }


}