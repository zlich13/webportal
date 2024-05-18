<?php
// Connect to the database
include('../../dbconnection.php');

// Check if the ID is set
if (isset($_POST['loc'])) {
    $loc = $_POST['loc'];
    $schedDate = $_POST['schedDate'];
    $from = $_POST['timeFrom'];
    $to = $_POST['timeTo'];
    $cap = $_POST['totalCount'];

    $sql = "SELECT room_id, room_name, room_capacity FROM classrooms WHERE room_id NOT IN (
                SELECT DISTINCT room_id FROM class_schedule WHERE sched_date = ? OR ( JSON_EXTRACT(repeating_data, '$.dow') LIKE CONCAT('%', DAYOFWEEK(?) - 1, '%') AND ? BETWEEN JSON_UNQUOTE(JSON_EXTRACT(repeating_data, '$.start')) AND JSON_UNQUOTE(JSON_EXTRACT(repeating_data, '$.end'))) AND ((time_from < ? AND time_to > ? AND time_from <> ? AND time_to <> ?)
                        OR (time_from < ? AND time_to > ?)
                        OR (time_from < ? AND time_to > ?)
                        OR (time_to = ? AND time_from < ? AND time_to <> ?)
                        OR (time_from <= ? AND time_to >= ? AND time_to <> ? AND time_from <> ?)
                      ))  AND location = ? AND room_capacity >= ? ";

    if (isset($_POST['hasLab'])) {       
        $has_lab = $_POST['hasLab'];
        if($has_lab == 1){
            $sql .= " AND room_name LIKE 'LAB%' ";
        }
    }

    $sql .= " ORDER BY room_name ASC";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssssssssssssssss", $schedDate, $schedDate, $schedDate, $to, $from, $from, $to, $from, $from, 
        $to, $to, $from, $from, $from, $from, $to, $to, $to, $loc, $cap);
    $stmt->execute();

    // Fetch the result
    $response = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'room_id' => $row['room_id'],
            'room_name' => $row['room_name'],
            'room_capacity' => $row['room_capacity']
        ];
    }
}

// Return the data as a JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>