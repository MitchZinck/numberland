<?php 
	ob_start();
	include "inc/global.php";
	include "inc/init.php";
	if(loggedIn() === true) {
		header("Location: index.php");
		exit();
	}
?>
<?php 
	$err = null;
	$errMessage = null;
	$errMsg = null;
	if(empty($_POST) === false) {
		require_once('inc/misc/recaptchalib.php');
	  	$resp = recaptcha_check_answer ($privatekey,
	                                $_SERVER["REMOTE_ADDR"],
	                                $_POST["recaptcha_challenge_field"],
	                                $_POST["recaptcha_response_field"]);

	  	if (!$resp->is_valid) {
	  		$errMessage = "Captcha wasn't entered correctly.";
	    	$err = "recaptchatable";
	    	//die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
	         //"(reCAPTCHA said: " . $resp->error . ")");
	  	} else {
			$requiredFields = array("email", "password", "confpassword");
			foreach($_POST as $key=>$value) {
				if(empty($value) && in_array($key, $requiredFields) === true) {
					$errors[] = $key;
				}
			}

			if(empty($errors) === true) {
				if(userExists($_POST['email'], $db) === true) {
					$errMessage = "Sorry that email is already taken.";
				} else if(preg_match("/\\s/", $_POST['email']) == true) {
					$errMessage = "Your email must not contain any spaces.";
					$err = "email";
				} else if(strlen($_POST['email']) > 45) {
					$errMessage = "email must be less than 15 characters.";
					$err = "password";
				} else if(strlen($_POST['password']) > 24 || strlen($_POST['password']) < 6) {
					$errMessage = "Password must be between 6 and 24 characters.";
					$err = "password";
				} else if($_POST['password'] !== $_POST['confpassword']) {
					$errMessage = "Sorry but the passwords do not match.";
					$err = "confpassword";
				} else if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
					$errMessage = "A valid email address is required.";
					$err = "email";
				} 
				// else if(emailExists($_POST['email'], $db) === true) {
				// 	$errMessage = "Sorry, that email is already in use.";
				// 	$err = "email";
				// }
			}
		}
	}
	
	if(isset($_GET['success']) && empty($_GET['success'])) {
		echo '<div id="account-box" class="container" style="width:800px"> 
				<div class="box-style style-addon" style="width:776px; text-align:center">
					<h2>You have successfully registered, an activation link has been sent to your email.</h2>
				</div>
			</div>';
	} else {
		if(empty($_POST) === false && empty($errors) === true && empty($errMessage) === true) {
			$registerData = array(
				"email" => $_POST['email'],
				"password" => $_POST['password'],
				"email_code" => md5($_POST['email'] . microtime()));
			registerUser($registerData, $db);
			header("Location: register.php?success");
			exit();
		} else {
			if(empty($errors) === false) {
				foreach($errors as $val) {
					$errMsg .= '$("#' . $val . '").css({"border": "5px solid red"});';
				} 
				$errMsg .= '$("<h2>Please fill in the required fields.</h2>").insertAfter("#register");';
			} else {
				$errMsg .= '$("#' . $err . '").css({"border": "5px solid red"});';
				$errMsg .= '$("<h2>' . $errMessage . '</h2>").insertAfter("#register");';
			}
			echo '<script>$(document).ready(function(){
								' . $errMsg . '
							});</script>';
		}
		echo $errMessage;
		include "inc/misc/registerform.php";
	}
?>
<?php 
	// include "includes/footer.php"; 
	ob_end_flush();
?>