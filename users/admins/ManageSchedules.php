<?php 

$page = "dashboard.php" ;
	  $label = "Manage Schedules";
	 $dstyle = "";
	  $astyle = "";
	  $msstyle = "active";
	  $drstyle = "";
	  $smsstyle = "";
	  $dmstyle = "";
	  session_start();
error_reporting(0);
	  ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | Officially Enrolled</title>
  <link rel="stylesheet" href="../../css/navigation.css"/>
  <link rel="stylesheet" href="../../css/style.css"/>
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!--icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
    <!-- CSS only -->

<style type="text/css">

.table{
	padding-right: 20px;
	width: 100%;
	border-collapse: collapse;

}

	
th, td{
	padding: 12px;
	text-align: center;
	border-bottom: 1px solid #ddd;
}

td{
	font-size: 14px;
	font-family: Arial, sans sans-serif;
}

#h1{
	font-weight: 600 ;
	text-align: center;
	background-color: grey;
	color: whitesmoke;
	padding: 10px 0px;
}

thead{
	background-color: var(--main-color);
	color: white;
}
/*
@media only screen and (max-width: 768px){

table{
			width: 90%;
	}
}*/

</style>
</head>

<body>

<?php 


include('superadminnav.php');?>


	<main>
		<table class="table">
			<thead>
				<tr>
				<th>Student#</th>
				<th>Name</th>
				<th>Course</th>
				<th>Email</th>
				<th>Phone</th>
				<th>View</th>
				</tr>
			</thead>
		<tbody>
			<?php 
			include('../../dbconnection.php');
	  		$sql= "select studentnum, CONCAT(lastname, ', ' , firstname, ' ' , mi , '.') as name, course, email, phone FROM `officially_enrolled`";
      		$result=$con->query($sql);

			while ($row = $result->fetch_assoc()) {

	echo "<tr>
			<td>" .$row["studentnum"] ."</td>
			<td>" .$row["name"] ."</td>
			<td>" .$row["course"] ."</td>
			<td>" .$row["email"] ."</td>
			<td>" .$row["phone"] ."</td>
			<td>
			 <a href = 'form.php' class = btn>Form</a> | 
			 <a href = 'form.php' class = btn>Docs</a>
			</td>
		</tr>";
}
			
			 ?>
			</tbody>
		
		</table>
	</main>
</body>

</html>

