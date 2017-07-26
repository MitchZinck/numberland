<?php
	function changePassword($password, $userId, $db) {
		$userId = (int) $userId;
		$password = md5($password);

		$query = $db->prepare("UPDATE `users` SET `password` = ? WHERE `user_id` = ?");
		$query->execute(array($password,$userId));
	}
	
	function canAccess($email, $db) {
		if(loggedIn() === true && userActive($email, $db) === true) {
			return true;
		}

		return false;
	}

	function activateUser($email, $emailCode, $db) {
		$query = $db->prepare("UPDATE `users` SET `active` = 1 WHERE `email` = ? AND `email_code` = ?");
		$query->$execute(array($email, $emailCode));
	}

	function clothingExists($id, $db) {
		$query = $db->prepare("SELECT id FROM `clothing` WHERE id = ?");
		$query->execute(array($id));
		if($query->fetchColumn() <= 0) {
			return false;
		}
		return true;
	}

	function getOrdersLastId($db) {
		$query = $db->prepare("SELECT MAX(order_id) FROM `orders`");
		$query->execute();
		return $query->fetchColumn();

	}

	function newNumber($db, $email, $oid) {
		$email = urldecode($email);
		$query = $db->prepare("SELECT MAX(number) FROM `numbers`");
		$query->execute();
		$number = $query->fetchColumn() + 1;

		$query = $db->prepare("SELECT `id` FROM `orders` WHERE `order_id` = ?");
		$query->execute(array($oid));
		$ids = array();
		$ids = $query->fetchAll();
		$inQuery = "";
		for($i = 0; $i < sizeof($ids); $i++) {
			$inQuery .= $ids[$i]['id'] . ",";
		}
		$inQuery = rtrim($inQuery,',');
		$db->exec("UPDATE `orders` SET `number_id` = " . $number . " WHERE `id` in (" . $inQuery. ")");

		$query = $db->prepare("INSERT INTO `numbers` (number, email) VALUES (:number, :email)");
		$query->bindParam(':number', $number);
		$query->bindParam(':email', $email, PDO::PARAM_STR);
		$query->execute();
	}

	function getClothingNameById($id, $db) {
		$query = $db->prepare("SELECT `name` FROM `clothing` where `id` = ?");
		$query->execute(array($id));
		return $query->fetchColumn();

	}

	function getClothingDescById($id, $db) {
		$query = $db->prepare("SELECT `description` FROM `clothing` where `id` = ?");
		$query->execute(array($id));
		return $query->fetchColumn();

	}

	function getPrice($clothing_id, $db) {
		$query = $db->prepare("SELECT `price` FROM `clothing` WHERE `id` = ?");
	    $query->execute(array($clothing_id));

	    $price = $query->fetchColumn();

	    return $price;
	}


	function updateMatches($db, $info) {
		$query = $db->prepare("INSERT INTO `matches` (bluelogo, bluename, bluewl, redlogo, redname, redwl) 
								VALUES (:bluelogo, :bluename, :bluewl, :redlogo, :redname, :redwl)");
		$bluewins = $info->contestants->blue->wins . "W-" . $info->contestants->blue->losses . "L";
		$redwins = $info->contestants->red->wins . "W-" . $info->contestants->red->losses . "L";
		$bluelogo = $info->contestants->blue->logoURL;
		$redlogo = $info->contestants->red->logoURL;
		$bluename = $info->contestants->blue->name;
		$redname = $info->contestants->red->name;

		if(strlen($bluename) > 13) {
			$chars = explode(" ", $bluename);
			$bluename = "";

			foreach ($chars as $c) {
			  $bluename .= $c[0];
			}
		} elseif(strlen($redname) > 13) {
			$chars = explode(" ", $redname);
			$redname = "";

			foreach ($chars as $c) {
			  $redname .= $c[0];
			}
		}

		$query->bindParam(':bluelogo', $bluelogo);
		$query->bindParam(':bluename', $bluename);
		$query->bindParam(':bluewl', $bluewins);

		$query->bindParam(':redlogo', $redlogo);
		$query->bindParam(':redname', $redname);
		$query->bindParam(':redwl', $redwins);

		$query->execute();
	}

	function registerUser($registerData, $db) {
		$registerData['password'] = md5($registerData['password']);

		$data= '`' . implode('` , `', $registerData) . '`';
		$fields = '\'' . implode('\', \'', $registerData) . '\'';

		$query = $db->prepare("INSERT INTO `users` (email, password, email_code) VALUES (:email, :password, :email_code)");
		$query->bindParam(':email', $email);
		$query->bindParam(':password', $password);
		$query->bindParam(':email_code', $email_code);

		$email = $registerData['email'];
		$password = $registerData['password'];
		$email_code = $registerData['email_code'];
		$query->execute();

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		$headers .= 'To: '. $email . "\r\n";
		$headers .= 'From: Numberland <admin@numberland.com>' . "\r\n";

		sendMail($email, "Activation Email from NumberLand", "
				Hello " . $email . ",\n\n
				Please activate your account by clicking the link below.\n\n
				http://whatyearis.it/demo/activate.php?email=" . $email . "&email_code=" . $email_code, $headers);
	}

	function sendMail($reciever, $subject, $body, $headers) {
		mail($reciever, $subject, $body, $headers);
	}
	
	function getUserData($userId) {
		$data = array();
		$userId = (int) $userId;

		$funcNumArgs = func_num_args();
		$getFuncArgs = func_get_args();

		$db = $getFuncArgs[1];

		if($funcNumArgs > 1) {
			unset($getFuncArgs[0]);
			unset($getFuncArgs[1]);

			$fields = '`' . implode('`, `', $getFuncArgs) . '`';
			$query = $db->prepare("SELECT $fields FROM `users` WHERE `user_id` = ?");

			if($query->execute(array($userId))) {
				while($r = $query->fetch(PDO::FETCH_ASSOC)) {
				    return $r;
				}
			}
		}	
	}

	function emailExists($email, $db) {
		$query = $db->prepare("SELECT COUNT(`user_id`) FROM `users` WHERE `email` =  ?");

		if($query->execute(array($email))) {
			while($r = $query->fetch()) {
			    if($r[0] == 1) {
			    	return true;
			    }
			}
		}

		return false;
	}

	function getMatch($matchId, $db) {
		$query = $db->prepare("SELECT * FROM `matches` WHERE `match_id` = ?");
	    $query->execute(array($matchId));

	    return $query->fetch();
	}

	function loggedIn() {
		return isset($_SESSION['user_id']) ? true : false;
	}

	function userExists($email, $db) {
		$query = $db->prepare("SELECT COUNT(`user_id`) FROM `users` WHERE `email` =  ?");

		if($query->execute(array($email))) {
			while($r = $query->fetch()) {
			    if($r[0] == 1) {
			    	return true;
			    }
			}
		}

		return false;
	}

	function userActive($email, $db) {
		$query = $db->prepare("SELECT COUNT(`user_id`) FROM `users` WHERE `email` =  ? AND `active` = 1");

		if($query->execute(array($email))) {
			while($r = $query->fetch()) {
			    if($r[0] == 1) {
			    	return true;
			    }
			}
		}

		return false;
	}

	function getUserId($email, $db) {
		$query = $db->prepare("SELECT `user_id` FROM `users` WHERE `email` = ?");

		if($query->execute(array($email))) {
			return $query->fetch()[0];
		}

		return NULL;
	}

	function login($email, $password, $db) {
		$userId = getUserId($email, $db);

		$password = md5($password);

		$query = $db->prepare("SELECT COUNT(`user_id`) FROM `users` WHERE `email` = ? AND `password` = ?");

		if($query->execute(array($email, $password))) {
			while($r = $query->fetch()) {
			    if($r[0] == 1) {
			    	return $userId;
			    }
			}
		}

		return false;
	}

?>