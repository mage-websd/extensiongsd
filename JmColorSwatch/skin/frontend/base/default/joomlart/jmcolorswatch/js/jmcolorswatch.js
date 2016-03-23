(function($ja) {

    var defaults = {
        attribute:"size",
        mainmainproduct:null  
    }

    var jmproductdetail = function(elm,options){
        this.selectobj = {};
        this.element = $ja(elm);
        this.options = $ja.extend({}, defaults, options);
        this.initialize();
    }

    jmproductdetail.prototype = {
        initialize:function(){
            data = {};
            dls = this.element.find("dl");
            this.selectobj = $ja("select#attribute"+this.options.attributeid);
            newselect = $ja("<div/>",{
                id:"product"+this.options.attribute
            });
            if(this.selectobj && this.selectobj.length){

                $ja.each(this.options.attribute_arr,$ja.proxy(function(i,n){

                     newspan = $ja("<span/>",{
                       value:i
                     });

                     if(this.options.attribute_qtys[i] > 0 ){
                       newspan.css({cursor:"pointer"});
                       newspan.bind("click",$ja.proxy(function(e){
                            newselect.find("span").removeClass("active");
                            $ja(e.target).addClass("active"); 
                            this.options.attribute_qtys[i];
                            this.selectobj.val(i);
                            this.selectobj.change();
                            $("attribute"+this.options.attributeid).simulate('change');                      
                            url = baseurl+"/jmcolorswatch/index/index";
                            data["mainproduct"] = this.options.mainproduct;
                            data["attribute"] = this.options.attribute;
                            data["attributevalue"] = i;
                           // this.submiturl(url,data);
                       },this));
                     }else{
                       newspan.addClass("disabled"); 
                     }
                     newspan.html(n);
                     newspan.prependTo(newselect);

                },this));

                this.element.append(newselect);
                this.selectobj.hide();
            }    
        },

        submiturl:function(url,data){
            $ja.ajax({
                  type: 'POST',
                  url: url,
                  data: data,
                  success: function(result){

                  }
                  ,
                  dataType: "json"
                
            });
        }

    }
    $ja.fn.jmproductdetail = function(options){
      new jmproductdetail(this,options);
    }
    
})(jQuery);




/**
* Event.simulate(@element, eventName[, options]) -> Element
*
* - @element: element to fire event on
* - eventName: name of event to fire (only MouseEvents and HTMLEvents interfaces are supported)
* - options: optional object to fine-tune event properties - pointerX, pointerY, ctrlKey, etc.
*
* $('foo').simulate('click'); // => fires "click" event on an element with id=foo
*
**/
(function(){
  
  var eventMatchers = {
    'HTMLEvents': /^(?:load|unload|abort|error|select|change|submit|reset|focus|blur|resize|scroll)$/,
    'MouseEvents': /^(?:click|dblclick|mouse(?:down|up|over|move|out))$/
  }
  var defaultOptions = {
    pointerX: 0,
    pointerY: 0,
    button: 0,
    ctrlKey: false,
    altKey: false,
    shiftKey: false,
    metaKey: false,
    bubbles: true,
    cancelable: true
  }
  
  Event.simulate = function(element, eventName) {
  
    var options = Object.extend(Object.clone(defaultOptions), arguments[2] || { });
    var oEvent, eventType = null;
    
    element = $(element);
    
    for (var name in eventMatchers) {
      if (eventMatchers[name].test(eventName)) { eventType = name; break; }
    }

    if (!eventType)
      throw new SyntaxError('Only HTMLEvents and MouseEvents interfaces are supported');
    
    if (document.createEvent) {
      oEvent = document.createEvent(eventType);
      if (eventType == 'HTMLEvents') {
        oEvent.initEvent(eventName, options.bubbles, options.cancelable);
      }
      else {
        oEvent.initMouseEvent(eventName, options.bubbles, options.cancelable, document.defaultView,
          options.button, options.pointerX, options.pointerY, options.pointerX, options.pointerY,
          options.ctrlKey, options.altKey, options.shiftKey, options.metaKey, options.button, element);
      }
    
      element.dispatchEvent(oEvent);
    }
    else {
      options.clientX = options.pointerX;
      options.clientY = options.pointerY;
      oEvent = Object.extend(document.createEventObject(), options);
      element.fireEvent('on' + eventName, oEvent);
    }
    return element;
  }
  
  Element.addMethods({ simulate: Event.simulate });
})();