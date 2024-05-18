
<?php
//connect to database
	include('../../dbconnection.php');

// retrieve the username to delete from the AJAX POST request
$acronym = $_POST["acronym"];

// sanitize the input to prevent SQL injection
$acronym = mysqli_real_escape_string($con, $acronym);

// construct the SQL query to delete the user account
$sql = "DELETE FROM course_strand WHERE acronym = '$acronym'";

// execute the query
if (mysqli_query($con, $sql)) {
} else {
  echo "Error deleting course: " . mysqli_error($con);
}

?>