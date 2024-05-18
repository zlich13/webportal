<?php 
    // Connect to the database
include('../../dbconnection.php');

$data = array();

if (isset($_POST['course_id']) && isset($_POST['sem']) && isset($_POST['year']) && isset($_POST['student_num'])) {
    $course_id = $_POST['course_id'];
    $sem = $_POST['sem'];
    $year = $_POST['year'];
    $student_num = $_POST['student_num'];

    // Select all subjects matching the criteria
    $sql = "SELECT * FROM subjects WHERE sub_course = ? AND semester = ? AND sub_grade_year <= ?;";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('iii', $course_id, $sem, $year);

    // Execute the statement
    if ($stmt->execute()) {
        // Fetch the result
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $subject_id = $row['subject_id'];

            // Check if the subject is taken by the student
            $sql = "SELECT * FROM student_subjects WHERE student_num = ? AND subject_id = ?;";
            $stmt2 = $con->prepare($sql);
            $stmt2->bind_param('ii', $student_num, $subject_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            $isTaken = $result2->num_rows > 0;

            $data[] = [
                'status' => 'success',
                'subject_id' => $row['subject_id'],
                'subject_code' => $row['subject_code'],
                'subject_description' => $row['subject_description'],
                'units' => $row['units'],
                'has_lab' => $row['has_lab'],
                'is_taken' => $isTaken // Include the "is_taken" flag in the response
            ];
        }

        $response = array(
            'status' => 'success',
            'data' => $data
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Error executing the statement'
        );
    }
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Data incomplete'
    );
}

echo json_encode($response);

// Close the prepared statements
$stmt->close();
$stmt2->close();

// Close the database connection
$con->close();

 ?>