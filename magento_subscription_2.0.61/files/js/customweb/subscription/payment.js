if(typeof Customweb == 'undefined'){
	Customweb = {};
}

if(typeof Customweb.Subscription == 'undefined'){
	Customweb.Subscription = {};
}

Customweb.Subscription.Payment = Class.create({
	
	initialize : function(hiddenFieldsUrl, visibleFieldsUrl, processUrl, javascriptUrl, methodCode, subscriptionId){
		this.hiddenFieldsUrl = hiddenFieldsUrl;
		this.visibleFieldsUrl = visibleFieldsUrl;
		this.javascriptUrl = javascriptUrl;
		this.processUrl = processUrl;
		this.methodCode = methodCode;
		this.subscriptionId = subscriptionId;
		
		this.onOrderCreated = this.requestHiddenFields.bindAsEventListener(this);
		this.onReceivedHiddenFields = this.gatherHiddenFields.bindAsEventListener(this);
		this.onReceivedVisibleFields = this.displayVisibleFields.bindAsEventListener(this);
		this.onReceiveJavascript = this.runAjaxAuthorization.bindAsEventListener(this);
		
		$('customweb-subscription-place-order').observe('click', this.createOrder.bind(this));
		
		if ($$('#alias_select select')[0]) {
			$$('#alias_select select')[0].observe('change', this.loadAliasData.bind(this));
		}
	},
	
	isAuthorization : function(method){
		if($(this.methodCode + '_authorization_method').value == method){
			return true;
		}
		return false;
	},
	
	displayVisibleFields : function(transport){
		var container = $('payment_form_fields_' + this.methodCode);
		container.update(transport.responseText);
	},
	
	requestHiddenFields : function(transport){
		if (this.isAuthorization('hidden')) {
			new Ajax.Request(this.hiddenFieldsUrl, {
			  onSuccess: this.onReceivedHiddenFields,
			  parameters: {subscription_id: this.subscriptionId},
			});
		} else if (this.isAuthorization('ajax')) {
			new Ajax.Request(this.javascriptUrl, {
				onSuccess: this.onReceiveJavascript,
				parameters: {subscription_id: this.subscriptionId},
			});
		} else {
			this.sendFieldsToUrl(this.processUrl);
		}
	},
	
	gatherHiddenFields : function(transport) {
		  var formInfo = eval('(' + transport.responseText + ')');
		  
		  this.extendMaps(this.formFields, formInfo.fields);
	      this.sendFieldsToUrl(formInfo.actionUrl);
	},
	
	runAjaxAuthorization : function(transport){
		 var data = eval('(' + transport.responseText + ')');
		 
		 if(data.error == 'no'){
			 var javascriptUrl = data.javascriptUrl;
			 var callbackFunction = data.callbackFunction;
			 
			 this.loadJavascript(javascriptUrl, (function(){
				 callbackFunction(this.formFields);
			 }).bind(this));
		 }
		 else{
			 alert(data.message);
		 }
	},
	
	createOrder : function (){
		if(this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')){
			try{
				this.savePaymentInfoInBrowser();
			}
			catch(err){
				return;
			}
		
			var form = $('customweb-subscription-payment-form');
			var formUrl = form.readAttribute('action');
			formUrl = formUrl.slice(0,-1);
			form.writeAttribute('action','javascript:void(0);');
			var request = new Ajax.Request(
				formUrl,
				{
				    method:'post',
				    parameters: {subscription_id: this.subscriptionId},
				    onSuccess: this.requestHiddenFields.bindAsEventListener(this),
				}
			);
			return false;
		}
	},
	
	sendFieldsToUrl : function(url){
		var tmpForm = new Element('form', {'action' : url, 'method' : 'post', 'id' : 'customweb_subscription_form'});
		$$('body')[0].insert(tmpForm);
		var fields = $H(this.formFields);
		fields.each(function(pair){
			tmpForm.insert(new Element('input', {'type':'hidden', 'name': pair.key, 'value': pair.value}));
		}, this);
		
		tmpForm.submit();
	},
	
	savePaymentInfoInBrowser : function(){
		// Validate forms
		eval('var result = ' + this.methodCode + 'validatePaymentFormElements();');
		if(result == false){
			throw 'invalid input';
		}
		
		// Get all form elements
		var fields = {};
		var tmp = '#payment_form_' + this.methodCode;
		var remove = this.methodCode + '[';
		
		var inputs = $$(tmp + ' input');
		inputs.each(function(i){
			if(i.type != 'hidden'){
				var name = i.name.replace(remove,"");
				name = name.replace("]","");
				fields[name] = i.value;
			}
		});
		
		var selects = $$(tmp + ' select');
		selects.each(function(s){
			var name = s.name.replace(remove,"");
			name = name.replace("]","");
			fields[name] = s.options[s.selectedIndex].value;
		});
		
		this.formFields = fields;
	},
	
	extendMaps : function(destination, source){
	    for (var property in source) {
	        if (source.hasOwnProperty(property)) {
	            destination[property] = source[property];
	        }
	    }
	    return destination;
	},
	
	loadJavascript : function(url,callback){ 
		 var head = document.getElementsByTagName("head")[0] || document.documentElement;
		 var script = document.createElement("script");
		 script.src = url;

		 // Handle Script loading
		 var done = false;

		 // Attach handlers for all browsers
		 script.onload = script.onreadystatechange = function() {
		     if ( !done && (!this.readyState ||
		             this.readyState === "loaded" || this.readyState === "complete") ) {
		         done = true;
		         callback();

		         // Handle memory leak in IE
		         script.onload = script.onreadystatechange = null;
		         if ( head && script.parentNode ) {
		             head.removeChild( script );
		         }
		     }
		 };

		 // Use insertBefore instead of appendChild  to circumvent an IE6 bug.
		 // This arises when a base node is used (#2709 and #4378).
		 head.insertBefore( script, head.firstChild );
	},
	
	loadAliasData : function(){
		var sel = $$('#alias_select select')[0];
		var value = sel.options[sel.selectedIndex].value;
		new Ajax.Request(this.visibleFieldsUrl, {
			  method: 'get',
			  parameters: 'alias_id=' + value + '&subscription_id=' + this.subscriptionId,
			  onSuccess: this.onReceivedVisibleFields
			});
	},
});