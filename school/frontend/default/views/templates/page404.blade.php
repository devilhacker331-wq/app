<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>

	<!-- Google font -->
	<link href="https://fonts.googleapis.com/css?family=Montserrat:700,900" rel="stylesheet">

	<!-- Font Awesome Icon -->
	<link type="text/css" rel="stylesheet" href="<?=base_url($frontendThemePath.'assets/404/css/font-awesome.min.css')?>" />

	<!-- Custom stlylesheet -->
	<link type="text/css" rel="stylesheet" href="<?=base_url($frontendThemePath.'assets/404/css/style.css')?>" />
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>

<body>

<div id="notfound">
	<div class="notfound-bg"></div>
	<div class="notfound">
		<div class="notfound-404">
			<h1>404</h1>
		</div>
		<h2>we are sorry, but the page you requested was not found</h2>
		<a href="<?=base_url('/frontend')?>" class="home-btn">Go Home</a>
		<a href="<?=base_url('/frontend/page/contact')?>" class="contact-btn">Contact us</a>
		<div class="notfound-social">
			<a href="#"><i class="fa fa-facebook"></i></a>
			<a href="#"><i class="fa fa-twitter"></i></a>
			<a href="#"><i class="fa fa-pinterest"></i></a>
			<a href="#"><i class="fa fa-google-plus"></i></a>
		</div>
	</div>
</div>

</body>

</html>
