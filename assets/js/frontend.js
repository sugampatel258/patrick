jQuery(".ajax_add_to_cart").on("click", function() {
	var product_id = jQuery(this).attr("data-product_id");
	var data = {
		'action': 'store_visited_products_cart',
		'product_id': product_id
	};
	jQuery.post(ajax_object.ajax_url, data, function(response) {
	});
});

jQuery('div.product-image').mouseover(function() {
    // var product_id = jQuery(this).parents().nextAll('a.ajax_add_to_cart').attr('data-product_id');
    var product_id = jQuery(this).attr('data-product_id');
    if(product_id) {
	    var data = {
			'action': 'store_visited_products',
			'product_id': product_id
		};
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		});
	}
});