<?php

// Connect to the database
include('../../../dbconnection.php');

if (isset($_POST['sched_id'])) {
    // Get the schedule ID from the request
    $sched_id = $_POST['sched_id'];

    // Collect other fields needed for the update
    $sched_date = $_POST['sched_date'];
    $sched_sub = $_POST['sched_subject'];
    $sched_sec = $_POST['sched_sections'];
    $sched_fac = $_POST['sched_faculty'];
    $sched_tfrom = $_POST['time_from'];
    $sched_tto = $_POST['time_to'];
    $sched_room = $_POST['sched_room'];
    $sched_weeks = $_POST['sched_weeks'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    

    // Create an SQL update query
    $sql = "UPDATE class_schedule SET sched_date = ?, subject = ?, sections = ?, faculty = ?, time_from = ?, time_to = ?, room = ? WHERE sched_id = ?";

    $stmt = $con->prepare($sql);

    // Bind parameters
    $stmt->bind_param("sssssssi", $sched_date, $sched_sub, $sched_sec, $sched_fac, $sched_tfrom, $sched_tto, $sched_room, $sched_id);

    // Execute the update
    if ($stmt->execute()) {
        $response = array(
            'status' => 'success',
            'message' => 'Schedule updated successfully'
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Failed to update schedule'
        );
    }

    $stmt->close();
} else {
    $response = array(
        'status' => 'error',
        'message' => 'No schedule id set'
    );
}

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

?>