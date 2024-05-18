<?php 
//connect to database
include('../../dbconnection.php');

$imageId = $_GET['id'];

$query = mysqli_query($con, "SELECT proof FROM transactions where user_id = $imageId");
$ret = mysqli_fetch_array($query);
$imageData = $ret['proof'];

// Output image
header('Content-Type: image/jpeg');
echo base64_decode($imageData);
?>