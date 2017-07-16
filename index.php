<!DOCTYPE html>
<html>
<head>
	<title>Donation Script</title>
</head>

<body>

<?php
	include 'inc/global.php';
	include 'inc/init.php';
?>
	
	<!-- <form method="POST" action="place_order.php">
		<select name="payment_method">
			<option value="pp">PayPal</option>
			<option value="btc">Bitcoin</option>
		</select>

		<hr/>

		<input name="iguname" type="text" placeholder="In-game username"></input> <br/>
		<input name="amount" type="text" placeholder="Amount (minimum: 100)"></input> <br/>

		<input type="submit" value="Purchase" />
	</form> -->

	<form method="POST" action="place_order.php">
		<select name="payment_method">
			<option value="pp">PayPal</option>
			<option value="btc">Bitcoin</option>
		</select>

		<hr/>

		<input name="email" type="text" placeholder="Email"></input> <br/>
		<input name="qty" type="text" placeholder="Quantity"></input> <br/>
		<input name="clothes[]" type="hidden" value="1"></input>
		<input name="1" type="hidden" value="3"></input> 
		<input name="clothes[]" type="hidden" value="2"></input> 
		<input name="2" type="hidden" value="6"></input> 

		<input type="submit" value="Purchase" /> 
	</form>

	<?php 
		if(loggedIn() === true) {
			include "inc/misc/loginbox.php";
		} else {
			include "inc/misc/sessionbox.php"; 
		}
	?>

</div>

</body>

</html>