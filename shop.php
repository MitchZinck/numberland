<?php
include 'inc/global.php';
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Vindred</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="../assets/css/main.css" />
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
	</head>
	<body class="two-sidebar">
		<div id="page-wrapper">

			<!-- Header -->
				<div id="header">
					<div class="container">

						<!-- Nav -->
              <?php
                include "../includes/header.php";
              ?>

					</div>
				</div>

			<!-- Main -->
				<div id="main">
					<div class="container">

						<div class="row">

							<!-- Content -->
								<div id="content" class="6u 12u(mobile) important(mobile)">
									<article>
										<header>
											<h2>Shop</h2>
											<span class="byline">Purchase Tokens here!</span>											
										</header>

													<form method="POST" action="place_order.php">
														

														<select name="payment_method">
															<option value="pp">PayPal/Credit</option>
															<option value="btc">Bitcoin</option>
														</select>

														<hr/>

														<input name="iguname" type="text" placeholder="In-game username"></input> <br/>
														<input name="amount" type="text" placeholder="Amount (minimum: 100)"></input> <br/>

														<input class ="button" type="submit" value="Purchase" />
													</form>


									</article>
								</div>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br>
							<h3>FAQ (Please read)</h3>
											<ul>
												<li>
													Q: What are tokens used for?
												</li>
												<li>
													A: Tokens are redeemed ingame for Tokkul and can be used to buy certain items. They can also be gambled, traded, dropped and looted.
												</li>
												<li>
													Q: Can I withdraw tokkul?
												</li>
												<li>
													A: Yes. Tokkul (or Tokens) can be withdraw from ingame as BTC. 100 Tokkul equals 0.005BTC. To withdraw Tokkul go to your quest tab and press the "withdraw" button. Withdrawals are manually
													done (for security measures) and may take up to 12 hours to process.
												</li>
												<li>
													Q: How do I claim my tokens once I pay for them?
												</li>
												<li>
													A: The next time you login they will be added to your inventory. You must have at least 1 free inventory slot before they are added.
												</li>
												<li>
													Q: Do I recieve a rank with buying tokens?
												</li>
												<li>
													A: Yes. Premium is 100 tokens. Super Premium is 1000 tokens. Extreme Premium is 10000 tokens. If you want to earn the Dicing rank please contact a admin on reddit or ingame.
												</li>
												<li>
													Q: Why are tokens so much more expensive when using Paypal?
												</li>
												<li>
													A: We only cashout in BTC and converting Paypal to BTC is complicated. To encourage people to use BTC over Paypal we have a 3.5:1 Paypal:BTC ratio.
												</li>
												<li>
													Q: Where can I get BTC easily?
												</li>
												<li>
													A: We recommend http://virwox.com/ for Paypal to BTC. If you have a credit card there are numerous sites that give you better deals than Virwox.
												</li>
											</ul>
		</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>

	</body>
</html>