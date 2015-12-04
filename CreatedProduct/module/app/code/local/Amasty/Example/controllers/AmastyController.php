<?php
/**
 * @copyright  Amasty (http://www.amasty.com)
 */ 
class Amasty_Example_AmastyController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		echo 'This is index controller. Please use specific actions below to create products. 
			<br/>
			<a href="/example/amasty/createSimpleProduct">Create Simple Product</a>
			<br/>
			<a href="/example/amasty/createConfProduct">Create Configurable Product</a>';
    }
	
    public function createSimpleProductAction()
    {
		$product = $this->_createProduct(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
		echo 'See <a href="/catalog/product/view/id/' . $product->getId() . '">created simple product</a>';
    }	

	
	public function createConfProductAction()
    {
		$simpleProduct = $this->_createProduct(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE); // create simple product
		$confProduct   = $this->_createProduct(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, false); // create conf product but do not save
		
		// associate simple (child) product to the configurable (parent) product.  There are several options, but not all of them works well
		// with recent CE 1.9+ Magento versions
	    // We will show you the easiest one. Just follow 5 next steps:

		// 1. select configurable attributes
		$colorAttributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', 'color');
		$confProduct->getTypeInstance()->setUsedProductAttributeIds(array($colorAttributeId)); 

		// 2. prepare information for each simple product
		$configurableProductsData = array();
		$configurableAttributesData = $confProduct->getTypeInstance()->getConfigurableAttributesAsArray();

		$simpleProductsData = array(
			'label'         => $simpleProduct->getAttributeText('color'),
			'attribute_id'  => $colorAttributeId,
			'value_index'   => (int) $simpleProduct->getColor(),
			'is_percent'    => 0,
			'pricing_value' => $simpleProduct->getPrice(),
		);

		$configurableProductsData[$simpleProduct->getId()] = $simpleProductsData;
		$configurableAttributesData[0]['values'][] = $simpleProductsData;

		// 3. set data in 2 required formats
		$confProduct->setConfigurableProductsData($configurableProductsData);
		$confProduct->setConfigurableAttributesData($configurableAttributesData);
		
		// 4. save product with special flag
		$confProduct->setCanSaveConfigurableAttributes(true);
		$confProduct->save();		
		
		//N.B. We need to have the same attribute set in both configurable product and it`s associated product.
		
		echo 'See <a href="/catalog/product/view/id/' . $confProduct->getId() . '">created configurable product</a>';
    }
	
	protected function _createProduct($type, $doSave=true) 
	{
		// required for some versions
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
	    $product = Mage::getModel('catalog/product');

	    // set madatory system attributes
		$rand = rand(1, 9999);
		$product
			->setTypeId($type)     // e.g. Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
			->setAttributeSetId(4) // default attribute set
			->setSku('example_sku' . $rand) // generate some random SKU 
			->setWebsiteIDs(array(1))
		;			

		// make the product visible
		$product
			->setCategoryIds(array(2,3))
			->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
			->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) // visible in catalog and search
		;
		// configure stock
		$product->setStockData(array(
			'use_config_manage_stock' => 1, // use global config ?
			'manage_stock'            => 0, // shoudl we manage stock or not?
			'is_in_stock'             => 1, 
			'qty'                     => 7,
        ));		
		
		// optimize performance, tell Magento to not update indexes
		$product
		    ->setIsMassupdate(true)
			->setExcludeUrlRewrite(true)
		;
		
		// finally set custom data
		$product
			->setName('Test Product #' . $rand) // add string attribute
			->setShortDescription('Description') // add text attribute
			
			// set up prices
			->setPrice(24.50)
			->setSpecialPrice(19.99)
			->setTaxClassId(2)    // Taxable Goods by default
			->setWeight(87)
		;
			
		// add dropdown attributes like brand, color or size
		$optionId = $this->_getOptionIDByCode('color', 'Black'); 
		$product->setColor($optionId);

		$optionId = $this->_getOptionIDByCode('size', 'M'); 
		$product->setSize($optionId);
		
	
		// add product images
		$images = array(
			'thumbnail'   => 'image.jpg',
			'small_image' => 'image.jpg',
			'image'       => 'image.jpg',
		);

		$dir = Mage::getBaseDir('media') . DS . 'example/amasty/';

		foreach ($images as $imageType => $imageFileName) {
			$path = $dir . $imageFileName;
			if (file_exists($path)) {
				try {
					$product->addImageToMediaGallery($path, $imageType, false);
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			} else {
				echo "Can not find image by path: `{$path}`<br/>";
			}
		}
		
		if ($doSave)
			$product->save();

		return $product;
	}
	
	
	
	protected function _getOptionIDByCode($attrCode, $optionLabel) 
	{
		$attrModel   = Mage::getModel('eav/entity_attribute');

		$attrID      = $attrModel->getIdByCode('catalog_product', $attrCode);
		$attribute   = $attrModel->load($attrID);

		$options     = Mage::getModel('eav/entity_attribute_source_table')
			->setAttribute($attribute)
			->getAllOptions(false);

		foreach ($options as $option) {
			if ($option['label'] == $optionLabel) {
				return $option['value'];
			}
		}

		return false;
	}


	
}