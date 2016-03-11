// Copyright CJM Creative Designs
// Function to switch list and grid view images

//Select the swatch based on shop-by selection
document.observe("dom:loaded", function() {
	if(query = window.location.search.substring(1)){
  		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var theGoods = vars[i].split("="),
				theQattcode = theGoods[0].trim(),
				theQval = theGoods[1].trim();
			$$('div.' + theQattcode).each(function(item) {
				var a = item.down(2).id,
					idee = a.split('-'),
					qAttId = idee[0],
					prodId = idee[2],
					g = $(qAttId + '-' + theQval + '-' + prodId),
					clickHandler = $(g).onclick;
				clickHandler.apply($(g));
			});
		} 
	}
});

function listSwitcher(a, id, src, lk) {
	
	//Set base image
	if (src) { $$('#the-' + id + ' a.product-image img').first().setAttribute("src", src); }
	
	//Set selected swatch
	$('ul-attribute' + lk + '-' + id).select('img', 'div').invoke('removeClassName', 'swatchSelected');
	a.addClassName('swatchSelected'); 
}

function setLocationConfig(url, id){
	var configQuery = '',
		cnt = 0;
    			
    $$('#' + id + ' div.swatch-category-container .swatch-category').each(function(name) {
  		if(name.hasClassName('swatchSelected')){
  			cnt++;
  			var pnt = name.up(0).className;
  			var pnt_split = pnt.split(" ");
  			if(cnt == 1) {
  				configQuery = '?' + pnt_split[1] + '=' + name.title;
  			} else {
	  			configQuery = configQuery + '&' + pnt_split[1] + '=' + name.title;
  			}
  		}
	});
	
	if(configQuery){
		window.location.href = url + configQuery;
	} else {
		window.location.href = url;
	}
}
