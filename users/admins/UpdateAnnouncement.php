<?php
//connect to database
	include('../../dbconnection.php');

// Check if the ID is set
if (isset($_POST['id'])) {
    // Get the ID from the POST data
    $id = $_POST['id'];
    $specificDate = ($availability == 'specificDate') ? mysqli_real_escape_string($con, $_POST['specificDate']) : null;

    // Get the record from the database based on the ID
    $sql = "SELECT * FROM announcements WHERE id = $id";
    $result = mysqli_query($con, $sql);

    // Check if the record exists
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $data = array(
            'announcement_type' => $row['announcement_type'],
            'announcement_title' => $row['announcement_title'],         
            'announcement_content' => $row['announcement_content'],
            'availability' => $row['availability'],
            $specificDate => $row['specific_date']
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