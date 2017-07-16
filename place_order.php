<?php
//error_reporting(0);
if (empty($_POST['payment_method']) || empty($_POST['email']) || empty($_POST['qty'])) {
	die('Please fill out all required fields.');
}

include 'inc/global.php';
include 'inc/init.php';
include_once("paypal/config.php");
include_once("paypal/paypal.class.php");

$method = ($_POST['payment_method'] == 'btc' ? 'btc' : 'pp');
$email  = $_POST['email'];
$clothes = $_POST['clothes'];

$oid = getOrdersLastId($db) + 1;
$totalPrice = 0;
$ppEcho = "";
$count = 1;
$price = 0;
$items = array();

foreach ($clothes as $key => $value) {
	$clothing_id = $value;
	$name = getClothingNameById($clothing_id, $db);
	$desc = getClothingDescById($clothing_id, $db);
	$qty = intval($_POST[$value]); 
	$price = getPrice($clothing_id, $db);
	$totalPrice += $price;

	$clothingArray = array("qty" => $qty,
						   "price" => $price,
						   "name" => $name,
						   "id" => $clothing_id,
						   "desc" => $desc);
	$items[] = $clothingArray;

	if ($qty < 1)
		die('Value must be 1 or higher.');

	// create new order
	$stmt = $db->prepare('INSERT INTO orders (order_id, clothing_id, email, qty, size) VALUES(:order_id, :clothing_id, :email, :qty, :size)');
	$stmt->bindParam(':order_id', $oid, PDO::PARAM_INT);
	$stmt->bindParam(':clothing_id', $clothing_id, PDO::PARAM_INT);
	$stmt->bindParam(':email', $email, PDO::PARAM_STR);
	$stmt->bindParam(':qty', $qty, PDO::PARAM_INT);
	$stmt->bindParam(':size', $size, PDO::PARAM_STR);
	$stmt->execute();

	$count++;
}


// process payment method
if ($method == 'pp'){
    $paypalmode = ($PayPalMode=='sandbox') ? '.sandbox' : '';

    $count = 0;
    $totalPrice = 0;
    $totalQty = 0;
    $paExtraData = "";

    for ($row = 0; $row < sizeof($items); $row++) {
        $qty = $items[$row]["qty"];
        $price = $items[$row]["price"];
        $itemName = $items[$row]["name"];
        $itemId = $items[$row]["id"];
        $itemDesc = $items[$row]["desc"];
        $totalPrice += ($price * $qty);
        $totalQty += $qty;
        $paExtraData .= '&L_PAYMENTREQUEST_0_NAME' . $count . '='.urlencode($itemName).
		                '&L_PAYMENTREQUEST_0_NUMBER' . $count . '='.urlencode($itemId).
		                '&L_PAYMENTREQUEST_0_DESC' . $count . '='.urlencode($itemDesc).
		                '&L_PAYMENTREQUEST_0_AMT' . $count . '='.urlencode($price).
		                '&L_PAYMENTREQUEST_0_QTY' . $count . '='. urlencode($qty);
        $count++;
    }

    //Other important variables like tax, shipping cost
    $totalTaxAmount      = $totalPrice * 0.15;  //Sum of tax for all items in this order. 
    //$handlingCost      = 0;  //Handling cost for this order.
    //$insuranceCost      = 0;  //shipping insurance cost for this order.
    $shippingDiscount    = -0;//$totalPrice > 50 ? -5.00 : -0:00; //Shipping discount for this order. Specify this as negative number.
    $shippingCost        = 5; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.
    
    //$grandTotal = ($totalPrice + $totalTaxAmount + $handlingCost + $insuranceCost + $shippingCost + $shippingDiscount);
    $grandTotal = ($totalPrice + $totalTaxAmount + $shippingCost + $shippingDiscount);
    
    //Parameters for SetExpressCheckout, which will be sent to PayPal
    $padata =   '&METHOD=SetExpressCheckout'.
                '&RETURNURL='.urlencode($PayPalReturnURL).
                '&CANCELURL='.urlencode($PayPalCancelURL).
                '&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
                $paExtraData .

                /* 
                //Override the buyer's shipping address stored on PayPal, The buyer cannot edit the overridden address.
                '&ADDROVERRIDE=1'.
                '&PAYMENTREQUEST_0_SHIPTONAME=J Smith'.
                '&PAYMENTREQUEST_0_SHIPTOSTREET=1 Main St'.
                '&PAYMENTREQUEST_0_SHIPTOCITY=San Jose'.
                '&PAYMENTREQUEST_0_SHIPTOSTATE=CA'.
                '&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=US'.
                '&PAYMENTREQUEST_0_SHIPTOZIP=95131'.
                '&PAYMENTREQUEST_0_SHIPTOPHONENUM=408-967-4444'.
                */
                
                '&NOSHIPPING=0'. //set 1 to hide buyer's shipping address, in-case products that do not require shipping
                
                '&PAYMENTREQUEST_0_ITEMAMT='.urlencode($totalPrice).
                '&PAYMENTREQUEST_0_TAXAMT='.urlencode($totalTaxAmount).
                '&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($shippingCost).
                //'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($handlingCost).
                '&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode($shippingDiscount).
                //'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($insuranceCost).
                '&PAYMENTREQUEST_0_AMT='.urlencode($grandTotal).
                '&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode).
                '&LOCALECODE=EN'. //PayPal pages to match the language on your website.
                '&LOGOIMG=http://whatyearis.it/images/logo.png'. //site logo
                '&CARTBORDERCOLOR=FFFFFF'. //border color of cart
                '&ALLOWNOTE=1';
                
                ############# set session variable we need later for "DoExpressCheckoutPayment" #######
           		$_SESSION['oid']     			=  $oid;
                $_SESSION['items']   			=  $items;
                $_SESSION['totalPrice']    		=  $totalPrice; //total amount of product; 
                $_SESSION['totalTaxAmount']     =  $totalTaxAmount;  //Sum of tax for all items in this order. 
                //$_SESSION['handlingCost']     =  $handlingCost;  //Handling cost for this order.
                //$_SESSION['insuranceCost']    =  $insuranceCost;  //shipping insurance cost for this order.
                $_SESSION['shippingDiscount']   =  $shippingDiscount; //Shipping discount for this order. Specify this as negative number.
                $_SESSION['shippingCost']       =  $shippingCost; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.
                $_SESSION['grandTotal']         =  $grandTotal;


        //We need to execute the "SetExpressCheckOut" method to obtain paypal token
        $paypal= new MyPayPal();
        $httpParsedResponseAr = $paypal->PPHttpPost('SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
        
        //Respond according to message we receive from Paypal
        if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
        {

                //Redirect user to PayPal store with Token received.
                $paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
                header('Location: '.$paypalurl);
             
        }else{
            //Show error message
            echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
            echo '<pre>';
            print_r($httpParsedResponseAr);
            echo '</pre>';
        }
}
else{
	require('inc/coinpayments.inc.php');
	$cps = new CoinPaymentsAPI();
	$cps->Setup($config['priv-key'], $config['pub-key']);


    $count = 0;
    $totalPrice = 0;
    $totalQty = 0;
    $orderDesc = "";

    for ($row = 0; $row < sizeof($items); $row++) {
        $qty = $items[$row]["qty"];
        $price = $items[$row]["price"];
        $itemName = $items[$row]["name"];
        $itemId = $items[$row]["id"];
        $itemDesc = $items[$row]["desc"];
        $totalPrice += ($price * $qty);
        $totalQty += $qty;

        $orderDesc .= $itemName . " x" . $qty + " | ";
        $count++;
    }

	$req = array(
		'qty' => $price,
		'currency1' => 'BTC',
		'currency2' => 'BTC',
		'address' => $config['btc-addr'], // send to address in the Coin Acceptance Settings page
		'item_name' => $orderDesc,
		'ipn_url' => $config['website-root'] . 'ipn_btc.php',
		'custom' => $oid
	);
	// See https://www.coinpayments.net/merchant-tools-api for all of the available fields
			
	$result = $cps->CreateTransaction($req);
	if ($result['error'] == 'ok') {
		$result = $result['result'];

		$stmt = $db->prepare('INSERT INTO transactions_bitcoin (oid, data) VALUES(:oid, :data)');

		$stmt->bindParam(':oid', $oid, PDO::PARAM_INT);
		$stmt->bindParam(':data', serialize($result), PDO::PARAM_STR);

		$stmt->execute();

		header('location: invoice.php?id=' . $oid);
	} else {
		print 'Error: '.$result['error']."\n";
	}
}

?>