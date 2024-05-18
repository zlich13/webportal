<?php
session_start();
error_reporting(0);

// Include database connection
include('../../dbconnection.php');

// Check if user is logged in
if (empty($_SESSION['uid'])) {
    echo 'error: User not logged in';
    exit;
}

// Check if announcement ID is provided
if (!isset($_POST['announcementId'])) {
    echo 'error: Announcement ID not provided';
    exit;
}

// Retrieve form data
$announcementId = $_POST['announcementId'];
$announcementTitle = $_POST['announcementTitle'];
$announcementContent = $_POST['announcementContent'];
$availability = $_POST['availability'];
$announcementType = $_POST['announcementType']; // Added line to retrieve announcement type

// Handle image upload if announcement type is image
if ($announcementType === 'image' && $_FILES['imageInput']['size'] > 0) {
    $uploadDir = '../../announcement-images/';
    $uploadedFile = $uploadDir . basename($_FILES['imageInput']['name']);
    $imageFileType = strtolower(pathinfo($uploadedFile, PATHINFO_EXTENSION));

    if ($_FILES['imageInput']['size'] > 5000000) { // 5MB limit
        echo 'error: File is too large. Maximum size allowed is 5MB.';
        exit;
    } elseif (!in_array($imageFileType, array('jpg', 'jpeg', 'png', 'gif'))) {
        echo 'error: Only JPG, JPEG, PNG, and GIF files are allowed.';
        exit;
    }

    if (move_uploaded_file($_FILES['imageInput']['tmp_name'], $uploadedFile)) {
        $imagePath = '../../announcement-images/' . basename($_FILES['imageInput']['name']);
        $updateQuery = "UPDATE announcements SET announcement_title = '$announcementTitle', announcement_content = '$announcementContent', availability = '$availability', announcement_type = '$announcementType', image_path = '$imagePath' WHERE id = $announcementId";
        if (mysqli_query($con, $updateQuery)) {
            echo 'success';
        } else {
            echo 'error: Unable to update announcement';
        }
    } else {
        echo 'error: Failed to upload file';
    }
} else {
    // Update announcement without image
    $updateQuery = "UPDATE announcements SET announcement_title = '$announcementTitle', announcement_content = '$announcementContent', availability = '$availability', announcement_type = '$announcementType' WHERE id = $announcementId";
    if (mysqli_query($con, $updateQuery)) {
        echo 'success';
    } else {
        echo 'error: Unable to update announcement';
    }
}

// Close database connection
mysqli_close($con);
?>