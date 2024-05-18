
<?php
//connect to database
	include('../../dbconnection.php');

// retrieve the username to delete from the AJAX POST request
$id = $_POST["id"];

// sanitize the input to prevent SQL injection
$id = mysqli_real_escape_string($con, $id);

// construct the SQL query to delete the user account
$sql = "DELETE FROM faculty WHERE id = '$id'";

// execute the query
if (mysqli_query($con, $sql)) {
} else {
  echo "Error deleting course: " . mysqli_error($con);
}

?>