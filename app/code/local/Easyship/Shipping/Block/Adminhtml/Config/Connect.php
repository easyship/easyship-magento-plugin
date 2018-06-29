<?php
/**
 * Class Easyship_Shipping_Block_Adminhtml_Config_Connect
 * Author: Easyship
 * Developer: Sunny Cheung, Holubiatnikova Anna, Aloha Chen, Phanarat Pak, Paul Lugangne Delpon
 * Version: 0.1.4
 * Author URI: https://www.easyship.com
*/

class Easyship_Shipping_Block_Adminhtml_Config_Connect extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_fieldRenderer;
    protected $_values;

    /**
     * Rendering the Form Field
     *
     * @param Varien_Data_Form_Element_Abstract
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        $setupready = false;
        $collection = Mage::getModel('oauth/consumer')->getCollection();


        foreach ($collection as $consumer) {
            if ($consumer->getName() == 'easyship') {
                $setupready = true;
                break;
            }
        }

        if ($setupready) {
            $html  .= '<h3>
                          Here are stores we found in your settings. Please select the store to integrate with Easyship.
                      </h3>';
            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $webgroup) {
                    $stores = $webgroup->getStores();
                    foreach ($stores as $store) {
                        $html .= $this->_getFieldHtml($element, $store);
                    }
                }
            }
        } else {
            $html .= $this->getInstruction();
        }


        $html .= $this->_getFooterHtml($element);

        return $html;

    }

    /**
     * Return an Instruction html text for first time user
     *
     * @return string
     *
     */
    protected function getInstruction()
    {
        $html = '
                  <h3>Easyship Plugin Setup Requirement</h3>
                 <div style="color:red; margin: 5px 0px;">OAuth Consumer named <strong> easyship</strong> is not found.  It is required to allow Easyship to retrieve information with Magento REST API. Please follow the instruction to setup your
                 Easyship OAuth consumer.</div>

                 <div style="display:block; background-color: white; border: 1px solid black; padding: 5px 10px;">
                 <h3> Instructions </h3>
                 <ol>
                    <li> 1. <strong>Create new Oauth Consumer</strong>:  Go to System > Web Services > REST Oauth Consumers, and create a new consumer name "easyship". </li>
                    <li> 2. <strong>Set up Role</strong>:  Go to System > Web Services > REST - Roles, and Click Add Admin Role.  In Role API Resource tab, you can choose All or select everything under "easyship" in Custom Settings. </li>
                    <li> 3. <strong>Set up permission attributes</strong>: Go to System > Web Services > REST - Attributes, choose the Admin role you created in Step 2.  In ACL Attributes rule tab, You can choose All or select everything under "easyship" in Custom Settings. </li>
                    <li> 4. <strong>Add role to your admin user</strong>:  Go to System > Permission > Users, select a user who will manage Shipping Information.  In REST role tab, select the role you added in Step 2. </li>
                    <li> 5. <strong>Connect your stores to Easyship</strong>: Go to System > Configuration > Easyship Settings > Activate Plugin.</li>
                    <li> 6. <strong>Register</strong>: Go through the registration flow.</li>
                </ol>
                </div>

                ';
        return $html;

    }

    /**
     * Render a field from custom template
     *
     * @return Mage_BlocMage_Adminhtml_Block_System_Config_Form_Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('easyship/adminhtml_config_generate');
        }
        return $this->_fieldRenderer;
    }

    /**
     * Not used for production
     * create default value for dropdown menu
     *
     * @return array
     *
     */
    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = array(
                array('label'=>Mage::helper('adminhtml')->__('No'), 'value'=>0),
                array('label'=>Mage::helper('adminhtml')->__('Yes'), 'value'=>1),
            );
        }
        return $this->_values;
    }

    /**
     * Not used for production
     * create enable field for Plugin
     *
     * @return string
     */

    protected function _getAciveFieldHtml($fieldset)
    {
        $configData = $this->getConfigData();

        $path = 'easyship_options/ec_shipping/active';

        if (isset($configData[$path])) {
            // access token is enabled
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = $this->getForm()->getConfigRoot()->descend($path);
            $inherit = true;
        }

        $field = $fieldset->addField('active', 'select',
            array(
                'name'  => 'groups[ec_shipping][fields][active][value]',
                'label' => 'Enable',
                'value' => $data,
                'values' => $this->_getValues(),
                'inherit' => $inherit

            ))->setRenderer( Mage::getBlockSingleton('adminhtml/system_config_form_field'));


        return $field->toHtml();
    }


    /**
     * Prepare Custom HTML template for field
     *
     * @param Mage_Adminhtml_Block_System_Config_Form_Fieldset
     * @param Mage_Core_Model_Store
     *
     * @return string
     */
    protected function _getFieldHtml($fieldset, Mage_Core_Model_Store $store)
    {
        $configData = $this->getConfigData();

        // config data for store
        // access token that retrieve from Easyship
        $path = 'easyship_options/ec_shipping/store_' . $store->getId();
        if (isset($configData[$path])) {
            // access token is enabled
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = $this->getForm()->getConfigRoot()->descend($path);
            $inherit = true;
        }

        $field = $fieldset->addField($store->getId(), 'text',
            array(
                'name'  => 'groups[ec_shipping][fields][store_'.$store->getId().'][value]',
                'label' => $store->getFrontendName(),
                'value' => $data,
                'inherit' => $inherit,
                'storeid' => $store->getId()

            ))->setRenderer($this->_getFieldRenderer());


        return $field->toHtml();

    }




}
