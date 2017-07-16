<?php 
	include "../init.php";
	 ?>

<div id="account-box" class="container" style="width:800px"> 
	<div class="box-style style-addon" style="width:776px; text-align:center">
		<h2><?php
				$error = $_SESSION['error'];
				echo $error;
				?>
		</h2>
	</div>
</div>


<?php //include "includes/footer.php"; ?>