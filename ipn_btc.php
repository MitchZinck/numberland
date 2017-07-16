<?php     
	include 'inc/global.php';

    // Fill these in with the information from your CoinPayments.net account. 
    $cp_merchant_id = $config['merchantid']; 
    $cp_ipn_secret = $config['secret']; 
    $cp_debug_email = 'mitchellzinck@yahoo.com'; 
     
    if (!isset($_POST['custom']))
    	errorAndDie('No order ID'); 

    $oid = $_POST['custom'];

    //These would normally be loaded from your database, the most common way is to pass the Order ID through the 'custom' POST field. 
    $order_currency = 'BTC'; 
    $order_total = 0.005; 
     
    function errorAndDie($error_msg) { 
        global $cp_debug_email; 
        if (!empty($cp_debug_email)) { 
            $report = 'Error: '.$error_msg."\n\n"; 
            $report .= "POST Data\n\n"; 
            foreach ($_POST as $k => $v) { 
                $report .= "|$k| = |$v|\n"; 
            } 
        } 
        die('IPN Error: '.$error_msg); 
    } 
     
    if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') { 
        errorAndDie('IPN Mode is not HMAC'); 
    } 
     
    if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) { 
        errorAndDie('No HMAC signature sent.'); 
    } 
     
    $request = file_get_contents('php://input'); 
    if ($request === FALSE || empty($request)) { 
        errorAndDie('Error reading POST data'); 
    } 
     
    if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($cp_merchant_id)) { 
        errorAndDie('No or incorrect Merchant ID passed'); 
    } 
         
    $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret)); 
    if ($hmac != $_SERVER['HTTP_HMAC']) { 
        errorAndDie('HMAC signature does not match'); 
    } 
     
    // HMAC Signature verified at this point, load some variables. 

    $txn_id = $_POST['txn_id']; 
    $item_name = $_POST['item_name']; 
    $item_number = $_POST['item_number']; 
    $amount1 = floatval($_POST['amount1']); 
    $amount2 = floatval($_POST['amount2']); 
    $currency1 = $_POST['currency1']; 
    $currency2 = $_POST['currency2']; 
    $status = intval($_POST['status']); 
    $status_text = $_POST['status_text']; 

    //depending on the API of your system, you may want to check and see if the transaction ID $txn_id has already been handled before at this point 

    // Check the original currency to make sure the buyer didn't change it. 
    if ($currency1 != $order_currency) { 
        errorAndDie('Original currency mismatch!'); 
    }     
     
    // Check amount against order total 
    if ($amount1 < $order_total) { 
        errorAndDie('Amount is less than order total!'); 
    }

	$stmt = $db->prepare('SELECT * FROM transactions_bitcoin WHERE oid=:oid');
	$stmt->bindParam(':oid', $oid, PDO::PARAM_INT);
	$stmt->execute();

	$x = $stmt->fetchALL(PDO::FETCH_ASSOC);

	$updateTransaction = $stmt->rowCount();

	// create/update user
	if ($updateTransaction <= 0){
		$stmt = $db->prepare('INSERT INTO transactions_bitcoin (oid, txn, item, price_usd, price_btc, status_code, status_text) VALUES(:oid, :txn, :item, :usd, :btc, :code, :status)');

		$stmt->bindParam(':oid', $oid, PDO::PARAM_INT);
		$stmt->bindParam(':txn', $txn_id, PDO::PARAM_STR);
		$stmt->bindParam(':item', $item_name, PDO::PARAM_STR);
		$stmt->bindParam(':usd', $amount1, PDO::PARAM_STR);
		$stmt->bindParam(':btc', $amount2, PDO::PARAM_STR);
		$stmt->bindParam(':code', $status, PDO::PARAM_INT);
		$stmt->bindParam(':status', $status_text, PDO::PARAM_STR);

		$stmt->execute();
	}
	else{
		$stmt = $db->prepare('UPDATE transactions_bitcoin SET txn=:txn, item=:item, price_usd=:usd, price_btc=:btc, status_code=:code, status_text=:status WHERE oid=:oid');

		$stmt->bindParam(':oid', $oid, PDO::PARAM_INT);
		$stmt->bindParam(':txn', $txn_id, PDO::PARAM_STR);
		$stmt->bindParam(':item', $item_name, PDO::PARAM_STR);
		$stmt->bindParam(':usd', $amount1, PDO::PARAM_STR);
		$stmt->bindParam(':btc', $amount2, PDO::PARAM_STR);
		$stmt->bindParam(':code', $status, PDO::PARAM_INT);
		$stmt->bindParam(':status', $status_text, PDO::PARAM_STR);

		$stmt->execute();
	}
   
    if ($status == 1) { 
		//end add transaction stuff
    } else if ($status < 0) { 
        //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent 
    } else { 
        //payment is pending, you can optionally add a note to the order page 
    } 
    die('IPN OK'); 