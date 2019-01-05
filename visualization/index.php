<!doctype html>
<html lang="pl">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" href="style.css">

		<!--Load the AJAX API-->
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<?php
		if(!($dwmyChoose = $_GET["dwmyChoose"]))
		{
			$dwmyChoose=0;
		}
		?>
		<?php include 'chart.php'; ?>

    <title>DDZ</title>
  </head>
  <body>

	<div class="container">
			<div class="row">
				<div class="col-sm menu-button"><a href="index.php?dwmyChoose=0"><div class="menu-item <?php if($dwmyChoose == 0) echo 'active' ?>">Dzień</div></a></div>
			  <div class="col-sm menu-button"><a href="index.php?dwmyChoose=1"><div class="menu-item <?php if($dwmyChoose == 1) echo 'active' ?>">Tydzień</div></a></div>
			  <div class="col-sm menu-button"><a href="index.php?dwmyChoose=2"><div class="menu-item <?php if($dwmyChoose == 2) echo 'active' ?>">Miesiąc</div></a></div>
			  <div class="col-sm menu-button"><a href="index.php?dwmyChoose=3"><div class="menu-item <?php if($dwmyChoose == 3) echo 'active' ?>">Rok</div></a></div>
			</div><br>
      <div class="row">
				<div class="col-sm menu-button"><a href="index.php?dwmyChoose=4"><div class="menu-item <?php if($dwmyChoose == 4) echo 'active' ?>">1 minuta</div></a></div>
			  <div class="col-sm menu-button"><a href="index.php?dwmyChoose=5"><div class="menu-item <?php if($dwmyChoose == 5) echo 'active' ?>">3 minuty</div></a></div>
			  <div class="col-sm menu-button"><a href="index.php?dwmyChoose=6"><div class="menu-item <?php if($dwmyChoose == 6) echo 'active' ?>">10 minut</div></a></div>
			</div>
			<div class="row">
				<div class="col-sm alert" id="ALERT_ID"></div>
			</div>
      <div class="row">
				<div class="col-sm chart" id="chart1"></div>
				<div class="col-sm chart" id="chart2"></div>
  			<div class="col-sm chart" id="chart3"></div>
			</div>
			<div class="row">
				<div class="col-sm chart" id="chart4"></div>
				<div class="col-sm chart" id="chart5"></div>
  			<div class="col-sm chart" id="chart6"></div>
			</div>
	</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  </body>
</html>
