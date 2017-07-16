<?php 
	include "inc/global.php";
	include "inc/init.php";
	if(loggedIn() === true) {
		header("Location: index.php");
	}

	if(isset($_GET['email'], $_GET['email_code']) === true) {
		$email = trim($_GET['email']);
		$email_code = trim($_GET['email_code']);

		if(emailExists($email, $db) === false) {
			header("Location: index.php");
			exit();
		} else {
			activateUser($email, $email_code, $db);
		}
	} else {
		header("Location: index.php");
		exit();
	}

	// include "includes/header.php";
	// include "includes/nav.php";
?>

<div id="account-box" class="container" style="width:800px"> 
	<div class="box-style style-addon" style="width:776px">
		<h2 style="float:left">Your account should be activated! Please try logging in. If you have any problems enter the IRC and ask for a admin or mod, or contact us through the Contact page.</h2>
	</div>
</div>

<?php //include "includes/footer.php"; ?>
