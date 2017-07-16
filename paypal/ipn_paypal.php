<?php
include '../inc/global.php';
include '../inc/init.php';
include_once("config.php");
include_once("paypal.class.php");

class PayPal_IPN{     
		
	function insert_data($response, $db){
		//insert into orderid the transaction id
		//have the order have a "confirmed" column

		$amount=$response['AMT'];
        $amount = str_replace("%2e",".", $amount);
		$currency=$response['CURRENCYCODE'];
		$payer_email=$response['EMAIL'];
		$first_name=$response['FIRSTNAME'];
		$last_name=$response['LASTNAME'];
		$country=$response['COUNTRYCODE'];
		$txn_id=$response['TRANSACTIONID'];
		//$txn_type=$this->issetCheck($post,'txn_type');
		$payment_status=$response['CHECKOUTSTATUS'];
		//$payment_type=$this->issetCheck($post,'payment_type');
		$payer_id=$response['PAYERID'];
		$create_date=date('Y-m-d H:i:s');
		$payment_date=date('Y-m-d H:i:s');

		$oid= $_SESSION['oid'];
		if ($oid<=0)
			die('invalid order');

		$stmt = $db->prepare('SELECT * FROM transactions_paypal WHERE `oid`=:oeyed');
		$stmt->bindParam(':oeyed', $oid, PDO::PARAM_STR);
		$stmt->execute();

		if($stmt->rowCount() > 0) {
			die("already there");
		}

        $data = implode(' | ', $response);
		$stmt = $db->prepare('INSERT INTO transactions_paypal (oid, payer_email, first_name, last_name, amount, currency, country, txn_id, payer_id, payment_status, create_date, payment_date, data) VALUES(:oid, :payer_email, :first_name, :last_name, :amount, :currency, :country, :txn_id, :payer_id, :payment_status, :create_date, :payment_date, :data)');
		$stmt->bindParam(':oid', $oid, PDO::PARAM_INT);
		$stmt->bindParam(':payer_email' , $payer_email , PDO::PARAM_STR);
		$stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
		$stmt->bindParam(':last_name' , $last_name , PDO::PARAM_STR);
		$stmt->bindParam(':amount' , $amount , PDO::PARAM_STR);
		$stmt->bindParam(':currency', $currency, PDO::PARAM_STR);
		$stmt->bindParam(':country' , $country , PDO::PARAM_STR);
		$stmt->bindParam(':txn_id' , $txn_id , PDO::PARAM_STR);
		$stmt->bindParam(':payer_id' , $payer_id , PDO::PARAM_STR);
		$stmt->bindParam(':payment_status' , $payment_status , PDO::PARAM_STR);
		$stmt->bindParam(':create_date' , $create_date , PDO::PARAM_STR);
		$stmt->bindParam(':payment_date' , $payment_date , PDO::PARAM_STR);
		$stmt->bindParam(':payment_date' , $payment_date , PDO::PARAM_STR);
        $stmt->bindParam(':data' , $data , PDO::PARAM_LOB);
		$stmt->execute();	
		
	}
}

//Paypal redirects back to this page using ReturnURL, We should receive TOKEN and Payer ID
if(isset($_GET["token"]) && isset($_GET["PayerID"]))
{
    //we will be using these two variables to execute the "DoExpressCheckoutPayment"
    //Note: we haven't received any payment yet.
    
    $token = $_GET["token"];
    $payer_id = $_GET["PayerID"];

    $items = $_SESSION['items'];
    $count = 0;
    $totalQty = 0;
    $paExtraData = "";

    for ($row = 0; $row < sizeof($items); $row++) {
        $qty = $items[$row]["qty"];
        $price = $items[$row]["price"];
        $itemName = $items[$row]["name"];
        $itemId = $items[$row]["id"];
        $itemDesc = $items[$row]["desc"];
        $totalQty += $qty;
        $paExtraData .= '&L_PAYMENTREQUEST_0_NAME' . $count . '='.urlencode($itemName).
                '&L_PAYMENTREQUEST_0_NUMBER' . $count . '='.urlencode($itemId).
                '&L_PAYMENTREQUEST_0_DESC' . $count . '='.urlencode($itemDesc).
                '&L_PAYMENTREQUEST_0_AMT' . $count . '='.urlencode($price).
                '&L_PAYMENTREQUEST_0_QTY' . $count . '='. urlencode($qty);
        $count++;
    }
    
    //get session variables
    $totalPrice         = $_SESSION['totalPrice']; //total amount of product;
    $totalTaxAmount     = $_SESSION['totalTaxAmount'] ;  //Sum of tax for all items in this order. 
    //$handlingCost      = $_SESSION['handlingCost'];  //Handling cost for this order.
    //$insuranceCost      = $_SESSION['insuranceCost'];  //shipping insurance cost for this order.
    $shippingDiscount   = $_SESSION['shippingDiscount']; //Shipping discount for this order. Specify this as negative number.
    $shippingCost       = $_SESSION['shippingCost']; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.
    $grandTotal         = $_SESSION['grandTotal'];

    $padata =   '&TOKEN='.urlencode($token).
                '&PAYERID='.urlencode($payer_id).
                '&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").       
                $paExtraData .
                '&PAYMENTREQUEST_0_ITEMAMT='.urlencode($totalPrice).
                '&PAYMENTREQUEST_0_TAXAMT='.urlencode($totalTaxAmount).
                '&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($shippingCost).
                //'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($handlingCost).
                '&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode($shippingDiscount).
                //'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($insuranceCost).
                '&PAYMENTREQUEST_0_AMT='.urlencode($grandTotal).
                '&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode);
    
    //We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
    $paypal= new MyPayPal();
    $httpParsedResponseAr = $paypal->PPHttpPost('DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
    
    //Check if everything went ok..
    if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
    {

            echo '<h2>Success</h2>';
            echo 'Your Transaction ID : '.urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);
            
                /*
                //Sometimes Payment are kept pending even when transaction is complete. 
                //hence we need to notify user about it and ask him manually approve the transiction
                */
                
                if('Completed' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"])
                {
                    echo '<div style="color:green">Payment Received! Your product will be sent to you very soon!</div>';
                }
                elseif('Pending' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"])
                {
                    echo '<div style="color:red">Transaction Complete, but payment is still pending! '.
                    'You need to manually authorize this payment in your <a target="_new" href="http://www.paypal.com">Paypal Account</a></div>';
                }

                // we can retrive transection details using either GetTransactionDetails or GetExpressCheckoutDetails
                // GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
                $padata =   '&TOKEN='.urlencode($token);
                $paypal= new MyPayPal();
                $httpParsedResponseAr = $paypal->PPHttpPost('GetExpressCheckoutDetails', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

                if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
                {
                    
                    echo '<br /><b>Stuff to store in database :</b><br /><pre>';
                    /*
                    #### SAVE BUYER INFORMATION IN DATABASE ###
                    //see (http://www.sanwebe.com/2013/03/basic-php-mysqli-usage) for mysqli usage
                    
                    $buyerName = $httpParsedResponseAr["FIRSTNAME"].' '.$httpParsedResponseAr["LASTNAME"];
                    $buyerEmail = $httpParsedResponseAr["EMAIL"];
                    
                    //Open a new connection to the MySQL server
                    $mysqli = new mysqli('host','username','password','database_name');
                    
                    //Output any connection error
                    if ($mysqli->connect_error) {
                        die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
                    }       
                    
                    $insert_row = $mysqli->query("INSERT INTO BuyerTable 
                    (BuyerName,BuyerEmail,TransactionID,itemName,itemId, ItemAmount,itemQty)
                    VALUES ('$buyerName','$buyerEmail','$transactionID','$itemName',$itemId, $totalPrice,$itemQty)");
                    
                    if($insert_row){
                        print 'Success! ID of last inserted record is : ' .$mysqli->insert_id .'<br />'; 
                    }else{
                        die('Error : ('. $mysqli->errno .') '. $mysqli->error);
                    }
                    
                    */

                    $obj = new PayPal_IPN();
                    $obj -> insert_data($httpParsedResponseAr, $db);
                    newNumber($db, $httpParsedResponseAr["EMAIL"], $_SESSION['oid']); //updates a new number
                    
                    echo '<pre>';
                    print_r($httpParsedResponseAr);
                    echo '</pre>';
                } else  {
                    echo '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
                    echo '<pre>';
                    print_r($httpParsedResponseAr);
                    echo '</pre>';

                }
    
    }else{
            echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
            echo '<pre>';
            print_r($httpParsedResponseAr);
            echo '</pre>';
    }
}
	
?>