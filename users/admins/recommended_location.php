<?php 

// Connect to the database
include('../../dbconnection.php');

    $sql = "SELECT DISTINCT location FROM classrooms";
    $result = mysqli_query($con, $sql);

    if ($result) {
        $locations = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $locations[] = $row['location'];
        }

            if (isset($_POST['facultyId'])) {
                $facultyId = $_POST['facultyId'];
                $schedDate = $_POST['schedDate'];
                $timeFrom = $_POST['timeFrom'];
                $timeTo = $_POST['timeTo'];

                $sql = "SELECT c.room_id, r.location FROM class_schedule c JOIN classrooms r ON c.room_id = r.room_id WHERE c.faculty_id = ? AND ( sched_date = ? OR ( JSON_EXTRACT(repeating_data, '$.dow') LIKE CONCAT('%', DAYOFWEEK(?) - 1, '%') AND ? BETWEEN JSON_UNQUOTE(JSON_EXTRACT(repeating_data, '$.start')) AND JSON_UNQUOTE(JSON_EXTRACT(repeating_data, '$.end')))) ORDER BY ABS(TIMEDIFF(c.time_from, ?)), ABS(TIMEDIFF(c.time_to, ?)) LIMIT 1;";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("isssss", $facultyId, $schedDate, $schedDate, $schedDate, $timeFrom, $timeTo);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc(); // Fetch a single row

                if ($row) {
                    $data = array(
                        'recommended' => $row['location'],
                        'locations' => $locations
                    );
                } else {
                    $data = array(
                        'recommended' => null,
                        'locations' => $locations
                    );
                }
                $response = array(
                    'status' => 'success',
                    'data' => $data
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'ID not set!'
                );
            }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'No locations'
        );
    }

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);


 ?>