<?php
	session_start();
	//error_reporting(0);

	require "global.php";
	require "misc/functions.php";

	if(loggedIn() === true) {
		$sessionUserId = $_SESSION['user_id'];
		$userData = getUserData($sessionUserId, $db, 'email', 'password', 'active');
		if(userActive($userData['email'], $db) === false) {
			session_destroy();
			header('Location: index.php');
			exit();
		}
	}

?>