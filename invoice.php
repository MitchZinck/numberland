<?php
	if (!isset($_GET['id']))
		die('');

	include 'inc/global.php';

	$oid = intval($_GET['id']);

	$stmt = $db->prepare('SELECT * FROM transactions_bitcoin WHERE oid=:oid');
	$stmt->bindParam(':oid', $oid, PDO::PARAM_INT);
	$stmt->execute();

	$order = $stmt->fetchALL(PDO::FETCH_ASSOC);
	$order = $order[0];

	if ($oid <= 0)
		exit;

	if (isset($_GET['do']))
	{
		$do=$_GET['do'];
		if ($do=='getstatus'){
			echo $order['status_code'];
		}
		exit;
	}

	if ($stmt->rowCount() <= 0)
		die('');

	$result = unserialize($order['data']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Donation Script</title>
</head>

<body>
<?php
	echo '<div align="center">';
	echo 'Please send ' . $result['amount'] . ' BTC to <strong>' . $result['address'] . '</strong> <br/><br/>';
	echo '<a target="_blank" href="' . $result['status_url'] . '">View Invoice Status</a> <br/>';
	echo '<img src="' . $result['qrcode_url'] . '" />';
	echo '</div>';
?>

<script src="https://code.jquery.com/jquery-1.11.3.js"></script>

<script>
setInterval(updateStatus, 5000);
function updateStatus(){
	var jqxhr = $.get( "invoice.php?id=<?php echo $oid;?>&do=getstatus", function() {
	}).done(function(data) {
		if (data=='100'){
			window.location = "thankyou.php"
		}
	})
}
</script>

</body>
</html>