<div class="box-style login-box" style="float: right">
	<h2 style="padding-right:5px; float:left">Welcome <strong><font color="#d22147">
		<?php 
		echo htmlentities($userData['email']); 
		if($userData['active'] == 0) {
			echo "<font color='red'> (Not Activated)</font>";
		}
		?>
	</font></strong> | <a href="inc/misc/logout.php">Logout</a> | <a href="inc/misc/changepass.php">Change Password</a></h2>
</div>