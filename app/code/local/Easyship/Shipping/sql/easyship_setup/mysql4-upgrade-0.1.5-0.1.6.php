<?php

$installer = $this;
$installer->startSetup();

$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', 'easyship_height');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
$attribute->setFrontendLabel(__('Easyship Height'))->save();

$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', 'easyship_width');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
$attribute->setFrontendLabel(__('Easyship Width'))->save();

$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', 'easyship_length');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
$attribute->setFrontendLabel(__('Easyship Length'))->save();

$installer->endSetup();