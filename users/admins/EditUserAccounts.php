<?php
//connect to database
	include('../../dbconnection.php');

// Check if the ID is set
if (isset($_POST['id'])) {
    // Get the ID from the POST data
    $id = $_POST['id'];

    // Get the record from the database based on the ID
    $sql = "SELECT * FROM user_accounts WHERE id = $id";
    $result = mysqli_query($con, $sql);

    // Check if the record exists
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $data = array(
            'username' => $row['username'],
            'email' => $row['email'],
            'type' => $row['account_type'],
            'image' => $row['image']
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