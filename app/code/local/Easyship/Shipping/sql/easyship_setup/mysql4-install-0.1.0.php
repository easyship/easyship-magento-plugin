<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$eavSetup = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$easyshipHeightData = array(
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

$eavSetup->addAttribute('catalog_product', 'easyship_height', $easyshipHeightData);

$easyshipWidthData = array(
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

$eavSetup->addAttribute('catalog_product', 'easyship_width', $easyshipWidthData);

$easyshipLengthData = array(
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

$eavSetup->addAttribute('catalog_product', 'easyship_length', $easyshipLengthData);

$easyshipCategoryData = array(
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

$eavSetup->addAttribute('catalog_product', 'easyship_category', $easyshipCategoryData);


$installer->endSetup();

