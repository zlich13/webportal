<?php
// Include database connection or any necessary includes
include('../../dbconnection.php');

// Assume $_POST['studentId'] contains the student ID sent via AJAX
$studentId = $_POST['studentId'];

// Fetch data from the database based on $studentId
$sql = "SELECT prelim, midterm, prefinal, final, final_grade, scale, remarks FROM grades WHERE student_num = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Encode the data as JSON and echo it back to the AJAX request
    echo json_encode($row);
} else {
    echo json_encode(array()); // Return empty JSON if no data found
}
?>
