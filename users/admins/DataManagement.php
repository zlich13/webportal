<?php 
session_start();
error_reporting(0);

$label = "Data Management";
$dmstyle = "active";

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
	<title>ACTS Web Portal | Data Management </title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<link rel="stylesheet" href="../../css/navigation.css"/>
  	<link rel="stylesheet" href="../../css/style.css"/>
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!--icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
</head>
<body>
	<?php include('NavigationBar.php');?>
	
			<main>
				<div>
        			<h5 class="title" id="main-title"><?php echo $label ?>:</h5>  
      			</div>
				 <div class="cards">
					<a id= "link"href="UserAccounts.php">
					<div class="card-single">
						<div >
							<span>User Accounts</span>
						</div>
						<div>
							<span class="las la-user-plus"></span>
						</div>
					</div>
					</a>
					<a id= "link"href="StudentList.php">
					<div class="card-single">
						<div>
							<span>Students List</span>
						</div>
						<div>
							<span class="las la-user-tie"></span>
						</div>
					</div>
					</a>
					<a id= "link"href="Courses.php">	
					<div class="card-single">
						<div>
							<span>Courses</span>
						</div>
						<div>
							<span class="las la-graduation-cap"></span>
						</div>
					</div>
					</a>
					<a id= "link"href="Subjects.php">
					<div class="card-single">
						<div>
							<span>Subjects</span>
						</div>
						<div>
							<span class="las la-book-open"></span>
						</div>
					</div>
					</a>
					<a id= "link"href="Faculty.php">
					<div class="card-single">
						<div>
							<span>Faculty</span>
						</div>
						<div>
							<span class="las la-chalkboard-teacher"></span>
						</div>
					</div>
					</a>
					<a id= "link"href="ClassRooms.php">
					<div class="card-single">
						<div>
							<span>Class Rooms</span>
						</div>
						<div>
							<span class="las la-university"></span>
						</div>
					</div>
					</a>
					<a id= "link"href="SchoolInfo.php">
					<div class="card-single">
						<div>
							<span>School Information</span>
						</div>
						<div>
							<span class="las la-info"></span>
						</div>
					</div>
					</a>
				</div> 
			</main>
			</body>
</html>
<?php } ?>

<style type="text/css">

.card-single{
	display: flex;
	justify-content: space-between;
	background: #cdfacf;
	color: var(--main-color);
}

</style>
