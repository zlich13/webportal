<?php
//connect to database
	include('../../dbconnection.php');

// retrieve the username to delete from the AJAX POST request
$username = $_POST["username"];

// sanitize the input to prevent SQL injection
$username = mysqli_real_escape_string($con, $username);

// construct the SQL query to delete the user account
$sql = "DELETE FROM user_accounts WHERE username = '$username'";

// execute the query
if (mysqli_query($con, $sql)) {
} else {
  echo "Error deleting user account: " . mysqli_error($con);
}

?>