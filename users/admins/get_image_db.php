<?php 
//connect to database
include('../../dbconnection.php');

$imageId = $_GET['id'];

$query = mysqli_query($con, "SELECT card_tor FROM applications WHERE app_id = $imageId");
$ret = mysqli_fetch_array($query);
$imageData = $ret['card_tor'];

// Output image
header('Content-Type: image/jpeg');
echo base64_decode($imageData);
?>