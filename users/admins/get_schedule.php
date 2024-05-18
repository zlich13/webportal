<?php

// Connect to the database
include('../../dbconnection.php');

if (isset($_POST['sched_id'])) {
    $sched_id = $_POST['sched_id'];
    $sql = "SELECT *,  FROM class_schedule WHERE sched_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $sched_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc(); // Fetch a single row
    if ($row) {
        if ($row['is_repeating'] == 1) {
            $jsonString = '{"dow":"1","start":"2023-05-01","end":"2023-07-17"}';
            $data = json_decode($jsonString);

            $dow = $data->dow;
            $start = $data->start;
            $end = $data->end;

        }
        $sql = "SELECT section_id FROM class_schedule_sections WHERE class_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $sched_id);
        $stmt->execute();
        // Fetch the result
        $sections = [];
        $result = $stmt->get_result();
        while ($sec_row = $result->fetch_assoc()) {
            $sections[] = $sec_row['section_id'];
        }
        $data = array(
            'subject' => $row['subject'],
            'sections' => $sections,
            'room' => $row['room_name']
        );
        $response = array(
            'status' => 'success',
            'data' => $data
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'No classes with that id'
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
