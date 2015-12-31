if(typeof Customweb == 'undefined'){
	var Customweb = {};
}

if(typeof Customweb.Subscription == 'undefined'){
	Customweb.Subscription = {};
}

Customweb.Subscription.TableForm = Class.create({	
	initialize : function(prefix, orderKey, onAddRowGetData, onValidate){
		var self = this;
		
		this.prefix = prefix;
		this.orderKey = orderKey;
		this.container = $(prefix + 'container');
		this.template = $(prefix + 'template');
		this.empty = $(prefix + 'empty');
		this.form = $(prefix + 'form');
		
		this.onAddRowGetData = onAddRowGetData || function(){};
		this.onValidate = onValidate || function(){ return true; };
		
		$(prefix + 'btn-add').observe('click', this.addRow.bind(this));
		this.form.select('.table-form-field').each(function(element, index){
			element.observe('keypress', function(event){
				if(event.keyCode == Event.KEY_RETURN) {
					self.addRow();
				}
			});
		});
		
		if (this.container.select('tbody').size() > 0) {
			this.empty.hide();
		}
	},

	addRow: function() {
		if (this.validateForm()) {
			var tmpl = new Template(this.template.innerHTML);
			
			var vars = {
				index: this.container.childElements().size()
			};
			this.form.select('.table-form-field').each(function(element, index){
				vars[element.readAttribute('data-name')] = element.value;
			});
			
			Object.extend(vars, this.onAddRowGetData() || {});
			
			if (this.orderKey) {
				var elementBefore,
					order = vars[this.orderKey],
					orderClass = this.prefix + 'data-' + this.orderKey;
				this.container.select('tbody').each(function(element, index){
					if (!elementBefore && element.select('.' + orderClass)[0].value > order) {
						elementBefore = element;
					}
				});
				if (elementBefore) {
					Element.insert(elementBefore, {before: tmpl.evaluate(vars)});
				} else {
					Element.insert(this.container, {bottom: tmpl.evaluate(vars)});
				}
			} else {
				Element.insert(this.container, {bottom: tmpl.evaluate(vars)});
			}
			
			this.empty.hide();
			
			this.form.select('.table-form-field').each(function(element, index){
				if (element.nodeName.toLowerCase() == 'select') {
					element.select('option')[0].selected = true;
				} else {
					element.value = '';
				}
			});
		}
	},
	
	validateForm: function() {
		var valid = true;
		
		this.form.select('.table-form-required').each(function(element, index){
			if (element.up().hasClassName('table-form-field-wrapper')) {
				element.up().removeClassName('validation-failed');
			} else {
				element.removeClassName('validation-failed');
			}
			if ($(element.id + '-advice')) {
				$(element.id + '-advice').hide();
			}
			if (element.value == '') {
				valid = false;
				if (element.up().hasClassName('table-form-field-wrapper')) {
					element.up().addClassName('validation-failed');
				} else {
					element.addClassName('validation-failed');
				}
				if ($(element.id + '-advice')) {
					$(element.id + '-advice').show();
				}
			}
		});
		
		return this.onValidate() && valid;
	},
	
	editRow: function(rowId) {
		var self = this;
		$(rowId).select('input[type=hidden]').each(function(element, index){
			var name = element.readAttribute('data-name').replace(new RegExp('_', 'g'), '-');
			$(self.prefix + 'form-' + name).value = element.value;
		});
		
		this.removeRow(rowId);
	},
	
	removeRow: function(rowId) {
		$(rowId).remove();
		
		if (this.container.select('tbody').size() == 0) {
			this.empty.show();
		}
	}
});