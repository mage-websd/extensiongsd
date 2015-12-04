<?php
class Sns_Ajaxfilter_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{

	public $_currentCategory;
	public $_searchSession;
	public $_productCollection;
	public $_maxPrice;
	public $_minPrice;
	public $_currMinPrice;
	public $_currMaxPrice;

	/*
	* 
	* Set all the required data that our slider will require
	* Set current _currentCategory, _searchSession, setProductCollection, setMinPrice, setMaxPrice, setCurrentPrices
	* 
	* @set all required data
	* 
	*/
	public function __construct(){

		$this->_currentCategory = Mage::registry('current_category');
		$this->_searchSession = Mage::getSingleton('catalogsearch/session');
		$this->setProductCollection();
		$this->setMinPrice();
		$this->setMaxPrice();
		$this->setCurrentPrices();

		parent::__construct();
	}

	/*
	*
	* Check whether the slider is enabled.
	*
	* @return boolean
	*
	*/
	public function getSliderStatus(){
		if(Mage::getStoreConfig('ajaxfilter_cfg/general/isenabled'))
			return true;
		else
			return false;
	}

	public function getGoBtnText(){
		$name = trim($this->getConfig('ajaxfilter_cfg/price_slider/goBtnText'));
		if($name == ''){
			$name = "Go";
		}
		return $name;
	}

	public function isTextBoxEnabled(){
		return $this->getConfig('ajaxfilter_cfg/price_slider/textbox');	
	}


	public function getPriceDisplayType(){
		if($this->isTextBoxEnabled()){
			$html = '
				<div class="text-box"><div class="price-from">
					'.$this->getCurrencySymbol().' <input type="text" name="min" id="minPrice" class="priceTextBox" value="'.$this->getCurrMinPrice().'"/></div><div class="price-separator">-</div><div class="price-to">'.$this->getCurrencySymbol().' <input type="text" name="max" id="maxPrice" class="priceTextBox" value="'.$this->getCurrMaxPrice().'"/></div>
					<input type="button" value="'.$this->getGoBtnText().'" name="go" class="go"/>
					<input type="hidden" id="amount" readonly="readonly" style="background:none; border:none;" value="'.$this->getCurrencySymbol().$this->getCurrMinPrice()." - ".$this->getCurrencySymbol().$this->getCurrMaxPrice().'" />

				</div>';
		}else{
			$html = '<p>
					<input type="text" id="amount" readonly="readonly" style="background:none; border:none;" value="'.$this->getCurrencySymbol().$this->getCurrMinPrice()." - ".$this->getCurrencySymbol().$this->getCurrMaxPrice().'" />
					</p>';
		}
		return $html;
	}

	/**
	*
	* Prepare html for slider and add JS that incorporates the slider.
	*
	* @return html
	*
	*/

	public function getHtml(){

		if($this->getSliderStatus()){
			$text='
				<div class="price">
					'.$this->getPriceDisplayType().'
					<div class="range-wrap"><div id="slider-range"></div></div>

				</div>'.$this->getSliderJs();

			return $text;
		}
		return parent::_toHtml();
	}

	/*
	* Prepare query string that was in the original url
	*
	* @return queryString
	*/
	public function prepareParams(){
		$url="";

		$params=$this->getRequest()->getParams();
		foreach ($params as $key=>$val)
			{
					if($key=='id'){ continue;}
					if($key=='min'){ continue;}
					if($key=='max'){ continue;}
					$url.='&'.$key.'='.$val;
			}
		return $url;
	}

	/*
	* Fetch Current Currency symbol
	*
	* @return currency
	*/
	public function getCurrencySymbol(){
		return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	}

	/*
	* Fetch Current Minimum Price
	*
	* @return price
	*/
	public function getCurrMinPrice(){
		if($this->_currMinPrice > 0){
			$min = $this->_currMinPrice;
		} else{
			$min = $this->_minPrice;
		}
		return $min;
	}

	/*
	* Fetch Current Maximum Price
	*
	* @return price
	*/
	public function getCurrMaxPrice(){
		if($this->_currMaxPrice > 0){
			$max = $this->_currMaxPrice;
		} else{
			$max = $this->_maxPrice;
		}
		return $max;
	}

	/*
	* Gives you the current url without parameters
	*
	* @return url
	*/
	public function getCurrentUrlWithoutParams(){
		$baseUrl = explode('?',Mage::helper('core/url')->getCurrentUrl());
		$baseUrl = $baseUrl[0];
		return $baseUrl;
	}

	/*
	* Check slider Ajax enabled
	*
	* @return boolean
	*/
	public function isAjaxSliderEnabled(){
		return $this->getConfig('ajaxfilter_cfg/ajax_conf/price');
	}

	/*
	* Get JS that brings the slider in Action
	*
	* @return JavaScript
	*/
	public function getSliderJs(){

		$baseUrl = $this->getCurrentUrlWithoutParams();

		if($this->isAjaxSliderEnabled()){
			$ajaxCall = 'ajaxFilter(url);';
		}else{
			$ajaxCall = 'window.location=url;';
		}

		if($this->isTextBoxEnabled()){
			$updateTextBoxPriceJs = '
							// Update TextBox Price
							$("#minPrice").val(newMinPrice); 
							$("#maxPrice").val(newMaxPrice);';
		}


		$html = '
			<script type="text/javascript">
				$sns_jq(function($) {
					var newMinPrice, newMaxPrice, url, temp;
					var categoryMinPrice = '.$this->_minPrice.';
					var categoryMaxPrice = '.$this->_maxPrice.';
					function isNumber(n) {
					  return !isNaN(parseFloat(n)) && isFinite(n);
					}

					$(".priceTextBox").focus(function(){
						temp = $(this).val();
					});

					$(".priceTextBox").keyup(function(){
						var value = $(this).val();
						if(!isNumber(value)){
							$(this).val(temp);
						}
					});

					$(".priceTextBox").keypress(function(e){
						if(e.keyCode == 13){
							var value = $(this).val();
							if(value < categoryMinPrice || value > categoryMaxPrice){
								$(this).val(temp);
							}
							url = getUrl($("#minPrice").val(), $("#maxPrice").val());
							'.$ajaxCall.'
						}
					});

					$(".priceTextBox").blur(function(){
						var value = $(this).val();
						if(value < categoryMinPrice || value > categoryMaxPrice){
							$(this).val(temp);
						}

					});

					$(".go").click(function(){
						url = getUrl($("#minPrice").val(), $("#maxPrice").val());
						'.$ajaxCall.'
					});

					$( "#slider-range" ).slider({
						range: true,
						min: categoryMinPrice,
						max: categoryMaxPrice,
						values: [ '.$this->getCurrMinPrice().', '.$this->getCurrMaxPrice().' ],
						slide: function( event, ui ) {
							newMinPrice = ui.values[0];
							newMaxPrice = ui.values[1];

							$( "#amount" ).val( "'.$this->getCurrencySymbol().'" + newMinPrice + " - '.$this->getCurrencySymbol().'" + newMaxPrice );

							'.$updateTextBoxPriceJs.'

						},stop: function( event, ui ) {

							// Current Min and Max Price
							var newMinPrice = ui.values[0];
							var newMaxPrice = ui.values[1];

							// Update Text Price
							$( "#amount" ).val( "'.$this->getCurrencySymbol().'"+newMinPrice+" - '.$this->getCurrencySymbol().'"+newMaxPrice );

							'.$updateTextBoxPriceJs.'

							url = getUrl(newMinPrice,newMaxPrice);
							if(newMinPrice != '.$this->getCurrMinPrice().' && newMaxPrice != '.$this->getCurrMaxPrice().'){
								clearTimeout(timer);
								//window.location= url;

							}else{
									timer = setTimeout(function(){
										'.$ajaxCall.'
									}, 200);
								}
						}
					});

					function getUrl(newMinPrice, newMaxPrice){
						return "'.$baseUrl.'"+"?min="+newMinPrice+"&max="+newMaxPrice+"'.$this->prepareParams().'";
					}
				});
			</script>
		';

		return $html;
	}


	/*
	* Get the Slider config
	*
	* @return object
	*/
	public function getConfig($key){
		return Mage::getStoreConfig($key);
	}
	
	
	/*
	* Set the Actual Min Price of the search and catalog collection
	*
	* @use category | search collection
	*/
	public function setMinPrice(){
		if( (isset($_GET['q']) && !isset($_GET['min'])) || !isset($_GET['q'])){
			$this->_minPrice = $this->_productCollection->getMinPrice();
			$this->_searchSession->setMinPrice($this->_minPrice);		
		}else{
			$this->_minPrice = $this->_searchSession->getMinPrice();	
		}
	}
	
	/*
	* Set the Actual Max Price of the search and catalog collection
	*
	* @use category | search collection
	*/
	public function setMaxPrice(){
		if( (isset($_GET['q']) && !isset($_GET['max'])) || !isset($_GET['q'])){
			$this->_maxPrice = $this->_productCollection->getMaxPrice();
			$this->_searchSession->setMaxPrice($this->_maxPrice);
		}else{
			$this->_maxPrice = $this->_searchSession->getMaxPrice();
		}
	}
	
	/*
	* Set the Product collection based on the page server to user 
	* Might be a category or search page
	*
	* @set /*
	* Set the Product collection based on the page server to user 
	* Might be a category or search page
	*
	* @set Mage_Catalogsearch_Model_Layer 
	* @set Mage_Catalog_Model_Layer    
	*/
	public function setProductCollection(){
		
		if($this->_currentCategory){
			$this->_productCollection = $this->_currentCategory
							->getProductCollection()
							->addAttributeToSelect('*')
							->setOrder('price', 'ASC');
		}else{
			$this->_productCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection()	
							->addAttributeToSelect('*')
							->setOrder('price', 'ASC');
		}					
	}
	
	
	/*
	* Set Current Max and Min Prices choosed by the user
	*
	* @set price
	*/
	public function setCurrentPrices(){
		
		$this->_currMinPrice = $this->getRequest()->getParam('min');
		$this->_currMaxPrice = $this->getRequest()->getParam('max'); 
	}	
	
	/*
	* Set Current Max and Min Prices choosed by the user
	*
	* @set price
	*/
	public function baseToCurrent($srcPrice){
		$store = $this->getStore();
        return $store->convertPrice($srcPrice, false, false);
	}
	
	
	/*
	* Retrive store object
	*
	* @return object
	*/
	public function getStore(){
		return Mage::app()->getStore();
	}
}
