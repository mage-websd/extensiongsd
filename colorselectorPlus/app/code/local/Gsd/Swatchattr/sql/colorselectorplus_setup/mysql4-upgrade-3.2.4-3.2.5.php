<?php

$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_product', 'cjm_useimages', array(

    'group'         				=> 'Images',
    'input'         				=> 'select',
    'type'          				=> 'int',
    'label'         				=> 'Use Images As Swatches?',
	'source'            			=> 'eav/entity_attribute_source_boolean',
	'frontend_class'				=> '',
    'backend'       				=> '',
	'frontend'						=> '',
	'default_value'					=> 0, 
    'visible'       				=> true,
    'required'      				=> false,
    'user_defined' 					=> true,
    'searchable' 					=> false,
    'filterable' 					=> false,
    'comparable'    				=> false,
    'visible_on_front' 				=> true,
    'visible_in_advanced_search'  	=> false,
    'is_html_allowed_on_front' 		=> false,
    'global'        				=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'note'							=> 'Do you want to use the products "Base Image For" image as the swatch?'
));

$installer->endSetup();