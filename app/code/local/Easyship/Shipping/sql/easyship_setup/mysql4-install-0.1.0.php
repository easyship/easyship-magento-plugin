<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$eavSetup = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$easyship_height_data = array(
    'type'      => 'int',
    'input'     => 'text',
    'global'    =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'  => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'used_in_product_listing' => true,
    'label' => 'Easyship Height (cm)'
);

$eavSetup->addAttribute('catalog_product', 'easyship_height', $easyship_height_data);

$easyship_width_data = array(
    'type'      => 'int',
    'input'     => 'text',
    'global'    =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'  => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'used_in_product_listing' => true,
    'label' => 'Easyship Width (cm)'
);

$eavSetup->addAttribute('catalog_product', 'easyship_width', $easyship_width_data);

$easyship_length_data = array(
    'type'      => 'int',
    'input'     => 'text',
    'global'    =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'  => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'used_in_product_listing' => true,
    'label' => 'Easyship Length (cm)'
);

$eavSetup->addAttribute('catalog_product', 'easyship_length', $easyship_length_data);

$easyship_category_data = array(
    'type'      => 'text',
    'input'     => 'select',
    'backend' => 'eav/entity_attribute_backend_array',
    'source' => 'easyship/source_categories',
    'global'    =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'  => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'used_in_product_listing' => true,
    'label' => 'Easyship Category'
);

$eavSetup->addAttribute('catalog_product', 'easyship_category', $easyship_category_data);


$installer->endSetup();

