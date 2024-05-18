<?php
// fetch_announcements.php

// Include necessary files and database connection code here (if not already included)
include('../../dbconnection.php');

// Fetch announcements from the database
$fetchQuery = "SELECT * FROM announcements";
$announcementsResult = mysqli_query($con, $fetchQuery);
$announcements = mysqli_fetch_all($announcementsResult, MYSQLI_ASSOC);

// Output the announcements as JSON
echo json_encode($announcements);
?>