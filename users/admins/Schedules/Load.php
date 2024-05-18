<?php
// Connect to the database
include('../../../dbconnection.php');

$data = array();

if (isset($_POST['option']) && isset($_POST['sem_id']) && isset($_POST['sy_id'])) {
    $option = $_POST['option'];
    $sem_id = $_POST['sem_id'];
    $sy_id = $_POST['sy_id'];

    if (!empty($option)) {
        $sql = "SELECT sched_id, subject, sched_date, repeating_data, is_repeating, time_from, time_to FROM class_schedule WHERE faculty_id = ? AND sy_id = ? AND sem_id = ? ORDER BY sched_id";

        $stmt = $con->prepare($sql);
        $stmt->bind_param('iii', $option, $sy_id, $sem_id);
        $stmt->execute();
        $stmt->store_result();

        $stmt->bind_result(
            $sched_id,
            $subject,
            $sched_date,
            $repeating_data,
            $is_repeating,
            $time_from,
            $time_to
        );

        $current_date = null; 

        while ($stmt->fetch()) {
            if ($is_repeating) {
                $repeating_data = json_decode($repeating_data, true);
                $start_date = new DateTime($repeating_data['start']);
                $end_date = new DateTime($repeating_data['end']);
                $dow = explode(',', $repeating_data['dow']);

                $current_date = $start_date;
                while ($current_date <= $end_date) {
                    $adj_dow = ($current_date->format('N') + 6) % 7 + 1;
                    if (in_array($adj_dow, $dow)) {
                        $sub_codes = array();
                        $sql2 = "SELECT DISTINCT subject_code FROM subjects WHERE subject_description = ?";
                        $stmt2 = $con->prepare($sql2);
                        $stmt2->bind_param('s', $subject);
                        $stmt2->execute();
                        $stmt2->bind_result($sub_code);
                        while ($stmt2->fetch()) {
                            $sub_codes[] = $sub_code;
                        }
                        $title = implode(', ', $sub_codes);

                        $start_date_time = new DateTime($current_date->format('Y-m-d') . ' ' . $time_from);
                        $end_date_time = new DateTime($current_date->format('Y-m-d') . ' ' . $time_to);

                        $data[] = array(
                            'id' => $sched_id,
                            'title' => $title,
                            'start' => $start_date_time->format('Y-m-d H:i:s'),
                            'end' => $end_date_time->format('Y-m-d H:i:s'),
                            'repeating' => 'Weekly Schedule'
                        );
                    }
                    $current_date->modify('+1 day');
                }
            } elseif ($sched_date) {
                $sub_codes = array();
                $sql2 = "SELECT DISTINCT subject_code FROM subjects WHERE subject_description = ?";
                $stmt2 = $con->prepare($sql2);
                $stmt2->bind_param('s', $subject);
                $stmt2->execute();
                $stmt2->bind_result($sub_code);
                while ($stmt2->fetch()) {
                    $sub_codes[] = $sub_code;
                }
                $title = implode(', ', $sub_codes);

                $start_date_time = new DateTime($sched_date . ' ' . $time_from);
                $end_date_time = new DateTime($sched_date . ' ' . $time_to);

                $data[] = array(
                    'id' => $sched_id,
                    'title' => $title,
                    'start' => $start_date_time->format('Y-m-d H:i:s'),
                    'end' => $end_date_time->format('Y-m-d H:i:s'),
                    'repeating' => ''
                );
            }
        }
        $stmt->close();
        $stmt2->close();
    }
}

$con->close();

echo json_encode($data);
?>
