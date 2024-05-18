<?php
session_start();
error_reporting(0);

include('../../dbconnection.php');

if (empty($_SESSION['uid'])) {
    header('Location: ../../logout.php');
    exit;
}

if (isset($_POST['announcementId'])) {
    $announcementId = $_POST['announcementId'];

    // Perform the deletion operation (adjust as needed)
    $deleteQuery = "DELETE FROM announcements WHERE id = '$announcementId'";
    $result = mysqli_query($con, $deleteQuery);

    if ($result) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
