<?php
// Connect to the database
include('../../../dbconnection.php');

if(isset($_POST["sched_id"])){
    $sched_id = $_POST['sched_id'];
    $sql = "DELETE from class_schedule WHERE sched_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $sched_id);
    if ($stmt->execute()) {
        $response = array(
            'status' => 'success',
            'message' => 'Schedule deleted successfully'
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Failed to delete schedule'
        );
    }
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