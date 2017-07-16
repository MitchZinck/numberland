<?php
	include "../global.php";
	include "../init.php";
	if(loggedIn() === true) {
		header("Location: ../../index.php");
		exit();
	}

	if(empty($_POST) === false) {
		$email = $_POST["email"];
		$password = $_POST["password"];

		if(empty($email) == true || empty($password) == true) {
			$error = "email/Password is incorrect";
		} else if(userExists($email, $db) === false) {
			$error = "Wrong email.";
		} else if(userActive($email, $db) === false) {
			$error = "Account is not activated. An activation email has been sent.";
		} else {
			$login = login($email, $password, $db);
			if($login == false) {
				$error = "email/Password is incorrect";
			} else {
				$_SESSION['user_id'] = $login;
				header('Location: ../../index.php');
				exit();
			}
		}
	} else {
		$error = "No data recieved, please try again?";
	}

	$_SESSION['error'] = $error;

	header('Location: error.php');
?>