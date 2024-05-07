<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>"
	  xml:lang="<?php echo $language; ?>">
<head>
	<title><?php echo $title; ?></title>
	<base href="<?php echo $base; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $css_url ?>"/>
</head>
<body>
<table width="80%">
	<tr>
		<td><?php if ($product_logo) { ?>
				<img class="product_logo" src="<?php echo $product_logo; ?>">
            <?php } ?>
		</td>
		<td class="reseller_details">
			<table>
				<tr>
					<td class="label"><?php echo $licensing_customer_name; ?></td>
					<td><?php echo $reseller_name; ?></td>
				</tr>
				<tr>
					<td class="label"><?php echo $licensing_email; ?></td>
					<td><?php echo $reseller_email; ?></td>
				</tr>
				<tr>
					<td class="label"><?php echo $licensing_address; ?></td>
					<td><?php echo $reseller_address; ?></td>
				</tr>
				<tr>
					<td class="label"><?php echo $licensing_reseller_contact; ?></td>
					<td><?php echo $reseller_contact; ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<h1>LICENSE CERTIFICATE</h1>

<table class="user-details" width="80%">
	<tr>
		<td colspan=2 class="table-heading"><?php echo $licensing_user_details; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_customer_name; ?></td>
		<td><?php echo $customer_name; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_email; ?></td>
		<td><?php echo $customer_email; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_address; ?></td>
		<td><?php echo $customer_address; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_customer_phone; ?></td>
		<td><?php echo $customer_phone; ?></td>
	</tr>
</table>
<br><br><br>
<table class="user-details" width="80%">
	<tr>
		<td colspan=2 class="table-heading"><?php echo $licensing_billing_details; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_order_number; ?></td>
		<td><?php echo $order_id; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_date; ?></td>
		<td><?php echo $date; ?></td>
	</tr>
</table>
<br><br><br>
<table class="user-details" width="80%">
	<tr>
		<td colspan=2 class="table-heading"><?php echo $licensing_license_details; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_product_name; ?></td>
		<td><?php echo $product_name; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_quantity; ?></td>
		<td><?php echo $quantity; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_validity_period; ?></td>
		<td><?php echo $validity_period; ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $licensing_keys; ?></td>
		<td><?php echo implode('<br>', $license_keys); ?></td>
	</tr>
</table>
<div class="footer-text">
    <?php echo $licensing_footer_text; ?>
</div>

</body>
</html>