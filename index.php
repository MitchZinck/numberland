<!DOCTYPE html>
<html>
<head>
	<title>Donation Script</title>
</head>

<body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="js/simpleCart.js"></script>
<script>
simpleCart({
    checkout: { 
        type: "SendForm" , 
        url: "http://whatyearis.it/demo/place_order1.php",
        method: "POST",
        extra_data: {
          payment_method: "",
          email: ""
        }
    } 
});

simpleCart.bind( 'beforeCheckout' , function( data ){
	data.payment_method = document.getElementById("payment_method").value;
	data.email = document.getElementById("email").value;
});
</script>

<?php
	include 'inc/global.php';
	include 'inc/init.php';
?>
	
	<div class="simpleCart_shelfItem">
		<h2 class="item_name"> Numberland Sweater </h2> 
	    <span class="item_price">$35.99</span> <br/>
	    <input class="item_quantity" type="text" placeholder="Quantity"></input> <br/>
	 	<select class="item_size">
	        <option value="Small"> Small </option>
	        <option value="Medium"> Medium </option>
	        <option value="Large"> Large </option>
	    </select> <br/>
	    <span style="display: none;" class="item_clothingid">1</span>
		<a class="item_add" href="javascript:;"> Add to Cart </a>
	</div>

	<div class="simpleCart_shelfItem">
		<h2 class="item_name"> Numberland Sweater 2 </h2> 
	    <span class="item_price">$12.99</span> <br/>
	    <input class="item_quantity" type="text" placeholder="Quantity"></input> <br/>
	 	<select class="item_size">
	        <option value="Small"> Small </option>
	        <option value="Medium"> Medium </option>
	        <option value="Large"> Large </option>
	    </select> <br/>
	    <span style="display: none;" class="item_clothingid">2</span>
		<a class="item_add" href="javascript:;"> Add to Cart </a>
	</div>

	<div class="simpleCart_shelfItem">
		<h2 class="item_name"> Numberland Sweater 3</h2> 
	    <span class="item_price">$60.99</span> <br/>
	    <input class="item_quantity" type="text" placeholder="Quantity"></input> <br/>
	 	<select class="item_size">
	        <option value="Small"> Small </option>
	        <option value="Medium"> Medium </option>
	        <option value="Large"> Large </option>
	    </select> <br/>
	    <span style="display: none;" class="item_clothingid">2</span>
		<a class="item_add" href="javascript:;"> Add to Cart </a>
	</div>
	<a href="javascript:;" class="simpleCart_empty"></a>
	<!-- show the cart -->
	<br><br><br>
	<div class="simpleCart_items"></div>
	<span class="simpleCart_quantity"></span> 
	items - <span class="simpleCart_total"></span><br>
	<select id="payment_method">
			<option value="pp">PayPal</option>
			<option value="btc">Bitcoin</option>
		</select><br>
	<input id="email" type="text" placeholder="Email"></input> <br/>
	<a href="javascript:;" class="simpleCart_checkout">Checkout</a>

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