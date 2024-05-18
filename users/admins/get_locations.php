<?php 
// Connect to database
include '../../dbconnection.php';

// Retrieve the selected faculty ID from the query parameter
$facultyId = $_GET['faculty'];
$start = $_GET['start'];

// Prepare and execute the SQL query to fetch available locations based on the selected faculty
$sql = "SELECT DISTINCT location FROM classrooms WHERE faculty_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $facultyId);
$stmt->execute();

// Fetch the locations from the result
$locations = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $locations[] = $row['location'];
}

// Return the locations as JSON response
header('Content-Type: application/json');
echo json_encode($locations);
?>