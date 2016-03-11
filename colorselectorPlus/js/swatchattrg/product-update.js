Product.Gallery.prototype.handleUploadComplete = function(files) {
        files.each( function(item) {
            if (!item.response.isJSON()) {
                try {
                    console.log(item.response);
                } catch (e2) {
                    alert(item.response);
                }
                return;
            }
            var response = item.response.evalJSON();
            if (response.error) {
                return;
            }
            var newImage = {};
            
            //Image Association
            	//var fn = item.name,
            		//us = fn.substr(fn.indexOf("_") + 1),
					//opid = us.substr(0, us.lastIndexOf('.')) || us,
					//isit = contains(cjm_options,opid),
					//doit = isit ? opid : '';
				
				//newImage.selectorbase = doit;
			/////////
            
            newImage.selectorbase = 0;
            newImage.selectormore = 0;
            newImage.url = response.url;
            newImage.file = response.file;
            newImage.label = '';
            newImage.position = this.getNextPosition();
            newImage.disabled = 0;
            newImage.defaultimg = 0;
            newImage.removed = 0;
            this.images.push(newImage);
            this.uploader.removeFile(item.id);
        }.bind(this));
        this.container.setHasChanges();
        this.updateImages();
};

Product.Gallery.prototype.updateImage = function(file) {
        var index = this.getIndexByFile(file);
        this.images[index].label = this
                .getFileElement(file, 'cell-label input').value;
        this.images[index].position = this.getFileElement(file,
                'cell-position input').value;
        this.images[index].removed = (this.getFileElement(file,
                'cell-remove input').checked ? 1 : 0);
        this.images[index].disabled = (this.getFileElement(file,
                'cell-disable input').checked ? 1 : 0);
        this.images[index].defaultimg = (this.getFileElement(file,
                'cell-defaultimg input').checked ? 1 : 0);
       	this.images[index].selectorbase = this.getFileElement(file,
                'cell-selectorbase select').value;
      	this.images[index].selectormore = this.getFileElement(file,
                'cell-selectormore select').value;
        this.getElement('save').value = Object.toJSON(this.images);
        this.updateState(file);
        this.container.setHasChanges();
};

Product.Gallery.prototype.updateVisualisation = function(file) {
        var image = this.getImageByFile(file);
        this.getFileElement(file, 'cell-label input').value = image.label;
        this.getFileElement(file, 'cell-position input').value = image.position;
        this.getFileElement(file, 'cell-remove input').checked = (image.removed == 1);
        this.getFileElement(file, 'cell-disable input').checked = (image.disabled == 1);
        this.getFileElement(file, 'cell-defaultimg input').checked = (image.defaultimg == 1);
        this.getFileElement(file, 'cell-selectorbase select').value = image.selectorbase;
        this.getFileElement(file, 'cell-selectormore select').value = image.selectormore;
        $H(this.imageTypes)
                .each(
                        function(pair) {
                            if (this.imagesValues[pair.key] == file) {
                                this.getFileElement(file,
                                        'cell-' + pair.key + ' input').checked = true;
                            }
                        }.bind(this));
        this.updateState(file);
};

Product.Configurable.prototype.initialize = function(attributes, links, idPrefix, grid, readonly) {
        this.templatesSyntax = new RegExp(
                '(^|.|\\r|\\n)(\'{{\\s*(\\w+)\\s*}}\')', "");
        this.attributes = attributes; // Attributes
        this.idPrefix = idPrefix; // Container id prefix
        this.links = $H(links); // Associated products
        this.newProducts = []; // For product that's created through Create
                                // Empty and Copy from Configurable
        this.readonly = readonly;

        /* Generation templates */
        this.addAttributeTemplate = new Template(
                $(idPrefix + 'attribute_template').innerHTML.replace(/__id__/g,
                        "'{{html_id}}'").replace(/ template no-display/g, ''),
                this.templatesSyntax);
        
        this.addValueTemplate = new Template(
                $(idPrefix + 'value_template').innerHTML.replace(/__id__/g,
                        "'{{html_id}}'").replace(/ template no-display/g, '').replace(/__pid__/g,"'{{html_parent_id}}'"),
                this.templatesSyntax);
        this.pricingValueTemplate = new Template(
                $(idPrefix + 'simple_pricing').innerHTML, this.templatesSyntax);
        this.pricingValueViewTemplate = new Template(
                $(idPrefix + 'simple_pricing_view').innerHTML,
                this.templatesSyntax);

        this.container = $(idPrefix + 'attributes');

        /* Listeners */
        this.onLabelUpdate = this.updateLabel.bindAsEventListener(this); // Update
                                                                            // attribute
                                                                            // label
        this.onValuePriceUpdate = this.updateValuePrice
                .bindAsEventListener(this); // Update pricing value
        this.onValueTypeUpdate = this.updateValueType.bindAsEventListener(this); // Update
                                                                                    // pricing
                                                                                    // type
        this.onValueDefaultUpdate = this.updateValueUseDefault
                .bindAsEventListener(this);

        /* Grid initialization and attributes initialization */
        this.createAttributes(); // Creation of default attributes

        this.grid = grid;
        this.grid.rowClickCallback = this.rowClick.bind(this);
        this.grid.initRowCallback = this.rowInit.bind(this);
        this.grid.checkboxCheckCallback = this.registerProduct.bind(this); // Associate/Unassociate
                                                                            // simple
                                                                            // product

        this.grid.rows.each( function(row) {
            this.rowInit(this.grid, row);
        }.bind(this));
};

Product.Configurable.prototype.createValueRow = function(container, value) {
        var templateVariables = $H( {});
        if (!this.valueAutoIndex) {
            this.valueAutoIndex = 1;
        }
        templateVariables.set('html_id', container.id + '_'
                + this.valueAutoIndex);
       	///
       	templateVariables.set('html_parent_id', container.id);
        templateVariables.set('value_index', value);
        ///
        
        templateVariables.update(value);
        var pricingValue = parseFloat(templateVariables.get('pricing_value'));
        if (!isNaN(pricingValue)) {
            templateVariables.set('pricing_value', pricingValue);
        } else {
            templateVariables.unset('pricing_value');
        }
        this.valueAutoIndex++;

        // var li = $(Builder.node('li', {className:'attribute-value'}));
        var li = $(document.createElement('LI'));
        li.className = 'attribute-value';
        li.id = templateVariables.get('html_id');
        li.update(this.addValueTemplate.evaluate(templateVariables));
        li.valueObject = value;
        if (typeof li.valueObject.is_percent == 'undefined') {
            li.valueObject.is_percent = 0;
        }

        if (typeof li.valueObject.pricing_value == 'undefined') {
            li.valueObject.pricing_value = '';
        }

        container.attributeValues.appendChild(li);

        var priceField = li.down('.attribute-price');
        var priceTypeField = li.down('.attribute-price-type');

        if (priceTypeField != undefined && priceTypeField.options != undefined) {
            if (parseInt(value.is_percent)) {
                priceTypeField.options[1].selected = !(priceTypeField.options[0].selected = false);
            } else {
                priceTypeField.options[1].selected = !(priceTypeField.options[0].selected = true);
            }
        }

        Event.observe(priceField, 'keyup', this.onValuePriceUpdate);
        Event.observe(priceField, 'change', this.onValuePriceUpdate);
        Event.observe(priceTypeField, 'change', this.onValueTypeUpdate);
        var useDefaultEl = li.down('.attribute-use-default-value');
        if (useDefaultEl) {
            if (li.valueObject.use_default_value) {
                useDefaultEl.checked = true;
                this.updateUseDefaultRow(useDefaultEl, li);
            }
            Event.observe(useDefaultEl, 'change', this.onValueDefaultUpdate);
        }
        
        var preselect = parseInt(container.attributeObject.preselect);
       	if (preselect)
       	{
       		var elements = li.getElementsByTagName('input');
       		var rad = container.id + '_label_preselect';
	       	for (var i=0; i<elements.length; ++i) {
	       		if (elements[i].name == rad) {
	       			if (elements[i].value == preselect) {
	       				elements[i].checked = true;
	       				elements[i].prevChecked = true;
	       				setTimeout(function (){
	       					var obj = elements[i];
	       					obj.checked = true;
	       					obj.prevChecked = true;
	       				},3000);
	       				break;
	       			}
	       		}
	       	}
       	}
};

function preSelectMe(element, pid, value_index, r)
{
    if (element.prevChecked) { element.checked=false; } 
    for (var i=0; i<r.length; ++i) { r[i].prevChecked = false; } 
    element.prevChecked = element.checked; 
    if (element.checked) {
        $(pid).attributeObject.preselect=value_index; } 
    else {
        $(pid).attributeObject.preselect=''; }
    superProduct.updateSaveInput(); 
}

function contains(a, obj) {
    for (var i = 0; i < a.length; i++) {
        if (a[i] === obj) {
            return true;
        }
    }
    return false;
}