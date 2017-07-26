<?php
include 'inc/global.php';
include 'inc/init.php';
include_once("paypal/config.php");
include_once("paypal/paypal.class.php");

	print_r($_POST);
	$totalPrice = 0;

	for ($i = 1; $i <= $_POST['itemCount']; $i++) {
		$arr = [];
		$arr = explode(":", $_POST['item_options_' . $i]);
		print_r($arr);
		$clothing_id = $arr[2];
	    if(!clothingExists($clothing_id, $db)) {
	        continue;
	    }
	    $name = getClothingNameById($clothing_id, $db);
	    $desc = getClothingDescById($clothing_id, $db);
	    $qty = intval($_POST['item_quantity_' . $i]); 
	    $price = getPrice($clothing_id, $db);
	    $totalPrice += $price;

		echo "<br>" . $clothing_id . "<br>" .
			 $name . "<br>" .
			 $desc . "<br>" .
			 $price . "<br>" .
			 $totalPrice . "<br>";
	}
?>