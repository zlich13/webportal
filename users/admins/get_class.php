<?php

// Connect to the database
include('../../dbconnection.php');

if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $sql = "SELECT cs.subject, r.room_name FROM class_schedule cs LEFT OUTER JOIN classrooms r ON cs.room_id=r.room_id WHERE sched_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc(); // Fetch a single row
    if ($row) {
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
