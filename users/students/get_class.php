<?php

// Connect to the database
include('../../dbconnection.php');

if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
        $sql = "SELECT sec.name, sec.year, sec.course, c.acronym FROM class_schedule_sections cs JOIN sections sec ON cs.section_id=sec.section_id JOIN course_strand c ON sec.course=c.id WHERE cs.class_id = ?";
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
            'sections' => implode(', ', $sections),
        );
        $response = array(
            'status' => 'success',
            'data' => $data
        );
    
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
