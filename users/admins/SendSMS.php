
<?php
session_start();
error_reporting(0);
$label = "Send SMS";
$smsstyle = "active";

include('../../dbconnection.php');

	if (strlen($_SESSION['uid']==0)) {
  header('location:../../logout.php');
  } else{

	  ?>

 
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>Acts Web Portal | Send SMS</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	  <link rel="stylesheet" href="../../css/navigation.css"/>
  <link rel="stylesheet" href="../../css/style.css"/>
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!--icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
</head>
<body>

	<?php 
	include('NavigationBar.php');
	?>

			<main>
				<div>
        			<h5 class="title" id="main-title"><?php echo $label ?>:</h5>  
      			</div>

				<div class="cards">
					<a href="Celebrants.php">
					<div class="card-single" style="background: #003f18;">
						<div >
							<h1 style="color: #fff;"><?php echo($enrolled)?></h1>
							<span style="color: #fff">Celebrants</span>
						</div>
						<div>
							<span class="las la-birthday-cake" style="color: #fff"></span>
						</div>
					</div>
					</a>
					<a href="AbsentsTardy.php">
					<div class="card-single" style="background: #ffcccb">
						<div>
							<h1><?php echo($rej_app)?></h1>
							<span>Absentees/Tardies</span>
						</div>
						<div>
							<span class="las la-user-times"></span>
						</div>
					</div>
					</a>
					<a href="CustomMessage.php">
					<div class="card-single"style="background: #cdfacf">
						<div>
							<h1><?php echo($pend_app)?></h1>
							<span>Custom Message</span>
						</div>
						<div>
							<span class="las la-comment"></span>
						</div>
					</div>
				</div>				
			</main>
		</div>
</body>

</html>

<?php } ?>

