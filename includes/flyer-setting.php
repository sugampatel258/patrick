<?php
function flyer_settings_callback() {
	$discountData = get_option('flyer_discounts');
	if(isset($_REQUEST['deleteDis']) && !empty($_REQUEST['deleteDis'])){
		unset($discountData[$_REQUEST['deleteDis']]);
		update_option('flyer_discounts', $discountData);
		echo "<script>location.href='admin.php?page=fyler-settings'</script>";
	}
?>
	<h1 class="wp-heading-inline">Flyer Settings</h1>
	<div class="wrap">
		<form method="post" id="flyer_setting">	
			<div class="form-group">
				<label for="order_amount">Order Amount: </label>
				<input type="text" class="form-control" name="order_amount" required placeholder="Amount is greater then">
			</div>
			<div class="form-group">
				<label for="order_amount">Discount Percentage: </label>
				<input type="text" class="form-control" name="discount_percentage" required placeholder="Discount Percent">
			</div>
			<div class="form-group">
				<input type="submit" name='setting_submit' value='Submit' class="button button-primary">
			</div>
		</form>
	</div>
<?php
	$discountData = get_option('flyer_discounts');
	if(!empty($discountData)) {
?>
	<h3>Flyer Discount</h3>
	<table class="bordered" >
		<thead>
			<tr>
				<th>Amount greater than</th>
				<th>Discount %</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($discountData as $amount => $discount) { ?>
			<tr>
				<td><?php echo $amount; ?></td>
				<td><?php echo $discount; ?></td>
				<td><a href="admin.php?page=fyler-settings&deleteDis=<?php echo $amount;?>"><img width="16" src='<?php echo MY_PLUGIN_PATH; ?>assets/images/delete.png'></a></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
<?php
	}
}

if( isset($_POST['setting_submit']) && !empty($_POST['setting_submit'])) {
	$amount = $_POST['order_amount'];
	$discount = $_POST['discount_percentage'];

	$flyerData = array($amount => $discount);

	$discountData = get_option('flyer_discounts');
	if(!empty($discountData)) {
		update_option('flyer_discounts', $flyerData + $discountData);
	} else {
		add_option('flyer_discounts', $flyerData);
	}
}
