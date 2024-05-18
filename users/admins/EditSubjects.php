<?php
//connect to database
include('../../dbconnection.php');

// Check if the ID is set
if (isset($_POST['id'])) {
    // Get the ID from the POST data
    $id = $_POST['id'];

    // Get the record from the database based on the ID
    $sql = "SELECT * FROM subjects WHERE subject_id = $id";
    $result = mysqli_query($con, $sql);

    // Check if the record exists
    if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Convert has_lab to 'Yes' or 'No'
    $has_lab_text = ($row['has_lab'] == 1) ? 'Yes' : 'No';

    $data = array(
        'desc' => $row['subject_description'],
        'code' => $row['subject_code'],
        'year' => $row['sub_grade_year'],
        'course' => $row['sub_course'],
        'units' => $row['units'],
        'has_lab' => $has_lab_text
    );
        $response = array(
            'status' => 'success',
            'data' => $data
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'No record found!'
        );
    }
} else {
    $response = array(
        'status' => 'error',
        'message' => 'ID not set!'
    );
}

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>