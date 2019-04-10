function sendFlyerMail(products, discounts, order_id) {
	// var prod = "<?php echo json_encode(); ?>"
	console.log(products);
	var data = {
		'action': 'flyer_on_mail',
		'products': products,
		'discount': discounts,
		'order_id': order_id
	};

	jQuery.post(ajax_object.ajax_url, data, function(response) {
	});
}