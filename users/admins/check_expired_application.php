<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

//Get timezone
date_default_timezone_set('Asia/Manila');

// Connect to database
include '/home/u955745524/domains/actswebportal.online/public_html/dbconnection.php';

// Get the current timestamp
$current_time = date('Y-m-d H:i:s');

// Get all applications that have been approved and have not been settled
$sql = "SELECT * FROM applications WHERE app_status = 2 AND expiry < ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $current_time);
mysqli_stmt_execute($stmt);

if(!$stmt) {
    die('Error: ' . mysqli_error($con));
}

$result = mysqli_stmt_get_result($stmt);

// Loop through the applications and mark them as expired if no transaction has been inserted
while ($row = mysqli_fetch_assoc($result)) {
    $user_id = $row['user_id'];
    $sql = "SELECT COUNT(*) as count FROM transactions WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    if(!$stmt) {
        die('Error: ' . mysqli_error($con));
    }

    $result2 = mysqli_stmt_get_result($stmt);
    $row2 = mysqli_fetch_assoc($result2);
    $transaction_count = $row2['count'];
    if ($transaction_count == 0) {
        $sql = "UPDATE applications SET app_status = 3, app_remarks = 'You have to resend your application since you have not settle your payment in the specified time.', app_remarks_date='$current_time' WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        if(!$stmt) {
            die('Error: ' . mysqli_error($con));
        }
    }
}
?>
