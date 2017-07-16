<?php 
	ob_start();
	include "../global.php";
	include "../init.php";
?>
<?php
	$err = null;
	$errMsg = null;
	$errMessage = null;
	if(empty($_POST) === false) {
		$requiredFields = array("currentpass", "newpassword", "againpassword");
		foreach($_POST as $key=>$value) {
			if(empty($value) && in_array($key, $requiredFields) === true) {
				$errors[] = $key;
			}
		}

		if(empty($errors) === true) {
			if(md5($_POST['currentpass']) !== $userData['password']) {
				$errMessage = "Password is incorrect.";
				$err = "currentpass";
			} else if(strlen($_POST['newpassword']) > 24 || strlen($_POST['newpassword']) < 6) {
				$errMessage = "Password must be between 6 and 24 characters.";
				$err = "newpassword";
			} else if(trim($_POST['newpassword']) !== trim($_POST['againpassword'])) {
				$errMessage = "Sorry but the passwords do not match.";
				$err = "againpassword";
			} 
		}
	}
		
	if(isset($_GET['success']) && empty($_GET['success'])) {
		echo '<div id="account-box" class="container" style="width:800px"> 
				<div class="box-style style-addon" style="width:776px; text-align:center">
					<h2>You have successfully changed your password. A email of the change has been sent to the accounts associated email.</h2>
				</div>
			</div>';
	} else {
		if(empty($_POST) === false && empty($errors) === true && empty($errMessage) === true) {
			changePassword($_POST['newpassword'], $sessionUserId, $db);
			header("Location: changepass.php?success");
			exit();
		} else {
			if(empty($errors) === false) {
				foreach($errors as $val) {
					$errMsg .= '$("#' . $val . '").css({"border": "5px solid red"});';
				} 
				$errMsg .= '$("<h2>Please fill in the required fields.</h2>").insertAfter("#change");';
			} else {
				$errMsg .= '$("#' . $err . '").css({"border": "5px solid red"});';
				$errMsg .= '$("<h2>' . $errMessage . '</h2>").insertAfter("#change");';
			}
			echo '<script>$(document).ready(function(){
								' . $errMsg . '
							});</script>';
		}

		echo '<div id="account-box" class="container" style="width:800px"> 
				<div class="box-style style-addon" style="width:776px">
					<form action="" method="post">
						<ul>
							<li style="float:left">
								<h2>Current Pass:</h2>
								<input type="password" name="currentpass" style="margin-right: 10px" id="currentpass">
							</li>
							<li>
								<h2>New Password:</h2>
								<input type="password" name="newpassword" id="newpassword">
							</li>
							<li>
								<h2>New Password Again:</h2>
								<input type="password" name="againpassword" id="againpassword">
							</li>
							<li>
								<button type="submit" id="change">Change Password</button>
							</li>
						</ul>
					</form>
				</div>
			</div>';
	}
?>
<?php 
	//include "includes/footer.php"; 
	ob_end_flush();
?>