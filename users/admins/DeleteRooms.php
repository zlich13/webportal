
<?php
//connect to database
	include('../../dbconnection.php');

// retrieve the username to delete from the AJAX POST request
$name = $_POST["name"];

// sanitize the input to prevent SQL injection
$name = mysqli_real_escape_string($con, $name);

// construct the SQL query to delete the user account
$sql = "DELETE FROM classrooms WHERE room_name = '$name'";

// execute the query
if (mysqli_query($con, $sql)) {
} else {
  echo "Error deleting course: " . mysqli_error($con);
}

?>