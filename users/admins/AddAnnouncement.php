<?php
session_start();
include('../../dbconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize the input data
    $announcementType = mysqli_real_escape_string($con, $_POST['announcementType']);
    $announcementTitle = mysqli_real_escape_string($con, $_POST['announcementTitle']);
    $announcementContent = mysqli_real_escape_string($con, $_POST['announcementContent']);
    $availability = mysqli_real_escape_string($con, $_POST['availability']);
    $specificDate = ($availability == 'specificDate') ? mysqli_real_escape_string($con, $_POST['specificDate']) : null;

    // Check if the announcement type is 'image'
    if ($announcementType === 'image') {
        // Handle image upload
        $uploadDir = '../../announcement-images/';
        $uploadFile = $uploadDir . basename($_FILES['imageInput']['name']);
    
        if (move_uploaded_file($_FILES['imageInput']['tmp_name'], $uploadFile)) {
            // Insert image path into the database
            $imagePath = mysqli_real_escape_string($con, $uploadFile);
            $insertQuery = "INSERT INTO announcements (announcement_type, announcement_title, announcement_content, availability, specific_date, image_path) 
                            VALUES ('$announcementType', '$announcementTitle', '$announcementContent', '$availability', '$specificDate', '$imagePath')";
    
            if (mysqli_query($con, $insertQuery)) {
                $lastInsertedID = mysqli_insert_id($con);
            echo $lastInsertedID;
            } else {
                echo "Error: " . $insertQuery . "<br>" . mysqli_error($con);
            }
        } else {
            echo "Error: Failed to move the uploaded file.";
        }
    } else {
        // Announcement type is 'text', insert data without image
        $insertQuery = "INSERT INTO announcements (announcement_type, announcement_title, announcement_content, availability, specific_date) 
                        VALUES ('$announcementType', '$announcementTitle', '$announcementContent', '$availability', '$specificDate')";
    
        if (mysqli_query($con, $insertQuery)) {
            $lastInsertedID = mysqli_insert_id($con);
            echo $lastInsertedID;
        } else {
            echo "Error: " . $insertQuery . "<br>" . mysqli_error($con);
        }
    }
} else {
    echo "Invalid request";
}


?>