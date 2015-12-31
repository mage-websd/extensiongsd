<?php

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2015 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category Customweb
 * @package Customweb_Subscription
 * @version 2.0.61
 */

/**
 * Setup model of customweb subscription module.
 *
 * @author Simon Schurter
 */
class Customweb_Subscription_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup {

	/**
	 * Prepare catalog attribute values to save
	 *
	 * @param array $attr
	 * @return array
	 */
	protected function _prepareValues($attr){
		$data = parent::_prepareValues($attr);
		$data = array_merge($data, 
				array(
					'frontend_input_renderer' => $this->_getValue($attr, 'input_renderer'),
					'is_global' => $this->_getValue($attr, 'global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL),
					'is_visible' => $this->_getValue($attr, 'visible', 1),
					'is_searchable' => $this->_getValue($attr, 'searchable', 0),
					'is_filterable' => $this->_getValue($attr, 'filterable', 0),
					'is_comparable' => $this->_getValue($attr, 'comparable', 0),
					'is_visible_on_front' => $this->_getValue($attr, 'visible_on_front', 0),
					'is_wysiwyg_enabled' => $this->_getValue($attr, 'wysiwyg_enabled', 0),
					'is_html_allowed_on_front' => $this->_getValue($attr, 'is_html_allowed_on_front', 0),
					'is_visible_in_advanced_search' => $this->_getValue($attr, 'visible_in_advanced_search', 0),
					'is_filterable_in_search' => $this->_getValue($attr, 'filterable_in_search', 0),
					'used_in_product_listing' => $this->_getValue($attr, 'used_in_product_listing', 0),
					'used_for_sort_by' => $this->_getValue($attr, 'used_for_sort_by', 0),
					'apply_to' => $this->_getValue($attr, 'apply_to'),
					'position' => $this->_getValue($attr, 'position', 0),
					'is_configurable' => $this->_getValue($attr, 'is_configurable', 1),
					'is_used_for_promo_rules' => $this->_getValue($attr, 'used_for_promo_rules', 0) 
				));
		return $data;
	}

	/**
	 * Default entites and attributes
	 *
	 * @return array
	 */
	public function getDefaultEntities(){
		return array(
			'catalog_product' => array(
				'entity_model' => 'catalog/product',
				'attribute_model' => 'catalog/resource_eav_attribute',
				'table' => 'catalog/product',
				'additional_attribute_table' => 'catalog/eav_attribute',
				'entity_attribute_collection' => 'catalog/product_attribute_collection',
				'attributes' => array(
					'is_subscription' => array(
						'type' => 'int',
						'label' => 'Enable Subscription',
						'input' => 'select',
						'source' => 'eav/entity_attribute_source_boolean',
						'required' => false,
						'sort_order' => 1,
						'apply_to' => 'simple,virtual,bundle,downloadable',
						'is_configurable' => false,
						'group' => 'Subscription' 
					),
					'subscription_infos' => array(
						'type' => 'text',
						'label' => 'Subscription',
						'input' => 'text',
						'backend' => 'eav/entity_attribute_backend_serialized',
						'required' => false,
						'sort_order' => 2,
						'apply_to' => 'simple,virtual,bundle,downloadable',
						'is_configurable' => false,
						'group' => 'Subscription' 
					),
					'subscription_plan' => array(
						'type' => 'int',
						'label' => 'Subscription Plan',
						'input' => 'select',
						'default' => 'None',
						'required' => false,
						'sort_order' => 3,
						'apply_to' => 'simple,virtual',
						'is_configurable' => true,
						'group' => 'Subscription' 
					) 
				) 
			) 
		);
	}
}
