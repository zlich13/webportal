<?php
// Connect to the database
include('../../dbconnection.php');

// Check if the ID is set
if (isset($_POST['facultyId'])) {
    $facultyId = $_POST['facultyId'];
    $schedDate = $_POST['schedDate'];

    $sql = "SELECT time_from, time_to FROM class_schedule WHERE faculty_id = ? AND ( sched_date = ? OR ( JSON_EXTRACT(repeating_data, '$.dow') LIKE CONCAT('%', DAYOFWEEK(?) - 1, '%') AND ? BETWEEN JSON_UNQUOTE(JSON_EXTRACT(repeating_data, '$.start')) AND JSON_UNQUOTE(JSON_EXTRACT(repeating_data, '$.end')) ) );";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("isss", $facultyId, $schedDate, $schedDate, $schedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $numRows = count($rows);

    if ($numRows > 0) {
        // Calculate the available time slots
        $morningAvailability = [['07:00', '12:00']];
        $afternoonAvailability = [['12:00', '19:00']];

        foreach ($rows as $schedule) {
            $startTime = strtotime($schedule['time_from']);
            $endTime = strtotime($schedule['time_to']);

            // Check if the schedule overlaps with the morning availability
            $tempMorningAvailability = [];
            foreach ($morningAvailability as $slot) {
                $slotStartTime = strtotime($slot[0]);
                $slotEndTime = strtotime($slot[1]);

                if ($startTime <= $slotEndTime && $endTime >= $slotStartTime) {
                    if ($startTime > $slotStartTime) {
                        $tempMorningAvailability[] = [date('H:i', $slotStartTime), date('H:i', $startTime)];
                    }
                    if ($endTime < $slotEndTime) {
                        $tempMorningAvailability[] = [date('H:i', $endTime), date('H:i', $slotEndTime)];
                    }
                } else {
                    $tempMorningAvailability[] = [date('H:i', $slotStartTime), date('H:i', $slotEndTime)];
                }
            }
            $morningAvailability = $tempMorningAvailability;
        }

        // Sort the morning availability based on time_from
        usort($morningAvailability, function ($a, $b) {
            return strtotime($a[0]) - strtotime($b[0]);
        });

        foreach ($rows as $schedule) {
            $startTime = strtotime($schedule['time_from']);
            $endTime = strtotime($schedule['time_to']);

            // Check if the schedule overlaps with the afternoon availability
            $tempAfternoonAvailability = [];
            foreach ($afternoonAvailability as $slot) {
                $slotStartTime = strtotime($slot[0]);
                $slotEndTime = strtotime($slot[1]);

                if ($startTime <= $slotEndTime && $endTime >= $slotStartTime) {
                    if ($startTime > $slotStartTime) {
                        $tempAfternoonAvailability[] = [date('H:i', $slotStartTime), date('H:i', $startTime)];
                    }
                    if ($endTime < $slotEndTime) {
                        $tempAfternoonAvailability[] = [date('H:i', $endTime), date('H:i', $slotEndTime)];
                    }
                } else {
                    $tempAfternoonAvailability[] = [date('H:i', $slotStartTime), date('H:i', $slotEndTime)];
                }
            }
            $afternoonAvailability = $tempAfternoonAvailability;
        }

        // Sort the afternoon availability based on time_from
        usort($afternoonAvailability, function ($a, $b) {
            return strtotime($a[0]) - strtotime($b[0]);
        });

        // Filter out empty slots and exclude invalid time slots
        $morningAvailability = array_filter($morningAvailability, function ($slot) {
            return $slot[0] !== $slot[1] && strtotime($slot[0]) < strtotime($slot[1]);
        });

        $afternoonAvailability = array_filter($afternoonAvailability, function ($slot) {
            return $slot[0] !== $slot[1] && strtotime($slot[0]) < strtotime($slot[1]);
        });

        $data = array(
            'morningAvailability' => array(),
            'afternoonAvailability' => array()
        );

        // Check if there are available time slots in the morning or afternoon
        if (count($morningAvailability) > 0 || count($afternoonAvailability) > 0) {
            // Build the morning availability array
            if (count($morningAvailability) > 0) {
                foreach ($morningAvailability as $slot) {
                    $data['morningAvailability'][] = '(' . $slot[0] . ' - ' . $slot[1] . ')';
                }
            } else {
                $data['morningAvailability'][] = 'Faculty not available';
            }

            // Build the afternoon availability array
            if (count($afternoonAvailability) > 0) {
                foreach ($afternoonAvailability as $slot) {
                    $data['afternoonAvailability'][] = '(' . $slot[0] . ' - ' . $slot[1] . ')';
                }
            } else {
                $data['afternoonAvailability'][] = 'Faculty not available';
            }
        } else {
            $data = array('availability' => 'Faculty not available for today');
        }

        $response = array(
            'status' => 'success',
            'data' => $data
        );
    } else {
        $data = array('availability' => 'Faculty available all day');
        $response = array(
            'status' => 'success',
            'data' => $data
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
