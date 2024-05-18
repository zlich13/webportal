
<?php
//connect to database
	include('../../dbconnection.php');

// retrieve the username to delete from the AJAX POST request
$code = $_POST["code"];

// sanitize the input to prevent SQL injection
$code = mysqli_real_escape_string($con, $code);

// construct the SQL query to delete the user account
$sql = "DELETE FROM subjects WHERE subject_code = '$code'";

// execute the query
if (mysqli_query($con, $sql)) {
} else {
  echo "Error deleting course: " . mysqli_error($con);
}

?>