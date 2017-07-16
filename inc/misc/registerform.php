<div id="account-box" class="container" style="width:656px"> 
	<div class="box-style style-addon" style="width:632px">
		<form action="" method="post">
			<ul style="float:left">
				<li style="float:left">
					<h2>Email:</h2>
					<input type="text" name="email" style="margin-right: 10px" id="email">
				</li>
				<li>
					<h2>Password:</h2>
					<input type="password" name="password" id="password">
				</li>

				<li style="float:left">
					<h2>Confirm Password:</h2>
					<input type="password" name="confpassword" id="confpassword">
				</li>
				<li style="float:left">
					<button type="submit" id="register">Register</button>
				</li>

			</ul>
			<div style="float:right; padding-right:10px">
			<?php
				require_once('recaptchalib.php');
		  		$publickey = "6Lc3FiMUAAAAAIsUbvhIwS21oywsIZUBoEBe4OuA"; 
		  		echo recaptcha_get_html($publickey);
			?></div>
		</form>
	</div>
</div>