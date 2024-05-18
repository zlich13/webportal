<?php

// Connect to the database
include('../../dbconnection.php');

if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $sql = "SELECT cs.*, sub.units, r.room_name FROM class_schedule cs LEFT OUTER JOIN subjects sub ON cs.subject=sub.subject_description LEFT OUTER JOIN classrooms r ON r.room_id=cs.room_id WHERE sched_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc(); // Fetch a single row
    if ($row) {
        if ($row['is_repeating']) {
            $repeating_data = $row['repeating_data'];
            $repeating_data = json_decode($repeating_data, true);
            $dayOfWeek = explode(',', $repeating_data['dow']);
        } elseif ($row['sched_date']) {
            $data[] = array(
                'id' => $sched_id,
                'title' => $title,
                'start' => $startDateTime->format('Y-m-d H:i:s'),
                'end' => $endDateTime->format('Y-m-d H:i:s'),
                'repeating' => ''
            );
        }









        $class_info = array(
            'units' => $row['units'],
            'subject' => $row['subject'],
            'room_name' => $row['room_name'],
            'time_from' => $row['time_from'],
            'time_to' => $row['time_to'],


        );
        $sql = "SELECT cs.section_id, sec.name, sec.year, sec.course, c.acronym FROM class_schedule_sections cs LEFT OUTER JOIN sections sec ON cs.section_id=sec.section_id LEFT OUTER JOIN course_strand c ON sec.course=c.id WHERE cs.class_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $class_id);
        $stmt->execute();
        // Fetch the result
        $sections = [];
        $result = $stmt->get_result();
        while ($sec_row = $result->fetch_assoc()) {
            $sections[] = $sec_row['acronym'] . "-" . $sec_row['year'] . $sec_row['name'];
        }
        $data = array(
            'subject' => $row['subject'],
            'sections' => implode(', ', $sections),
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
        'message' => 'No class id set'
    );
}


// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

?>
