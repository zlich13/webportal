<?php
// Connect to the database
include('../../dbconnection.php');

$data = array();

if (isset($_POST['user']) && isset($_POST['sem_id']) && isset($_POST['sy_id'])) {
    $user = $_POST['user'];
    $sem_id = $_POST['sem_id'];
    $sy_id = $_POST['sy_id'];
    $query = "SELECT cs.sched_id, cs.subject, cs.sched_date, cs.repeating_data, cs.is_repeating, cs.time_from, cs.time_to, f.prefix, f.fname, f.mname, f.lname, r.room_name FROM class_schedule cs JOIN class_schedule_sections css ON cs.sched_id = css.class_id JOIN sections sec ON css.section_id = sec.section_id JOIN student_section ssec ON sec.section_id = ssec.section_id JOIN student_subjects ssub ON ssec.student_num = ssub.student_num JOIN subjects subj ON ssub.subject_id = subj.subject_id JOIN faculty f ON cs.faculty_id=f.id JOIN classrooms r ON cs.room_id=r.room_id WHERE ssec.student_num = ? AND subj.subject_description = cs.subject AND cs.sy_id = ? AND cs.sem_id = ?";

    $statement = $con->prepare($query);
    $statement->bind_param('iii', $user, $sy_id, $sem_id);
    $statement->execute();
    $statement->store_result();

    $statement->bind_result(
        $sched_id,
        $subject,
        $sched_date,
        $repeating_data,
        $is_repeating,
        $time_from,
        $time_to,
        $prefix,
        $fname,
        $mname,
        $lname,
        $room
    );

    $currentDate = null; // Initialize currentDate outside the while loop

    while ($statement->fetch()) {
        if ($is_repeating) {
            $repeating_data = json_decode($repeating_data, true);
            $startDate = new DateTime($repeating_data['start']);
            $endDate = new DateTime($repeating_data['end']);
            $dayOfWeek = explode(',', $repeating_data['dow']);

            $currentDate = $startDate;
            while ($currentDate <= $endDate) {
                $adjustedDayOfWeek = ($currentDate->format('N') + 6) % 7 + 1;
                if (in_array($adjustedDayOfWeek, $dayOfWeek)) {
                    $subjectCodes = array();
                    $subjectQuery = "SELECT DISTINCT subject_code FROM subjects WHERE subject_description = ?";
                    $subjectStatement = $con->prepare($subjectQuery);
                    $subjectStatement->bind_param('s', $subject);
                    $subjectStatement->execute();
                    $subjectStatement->bind_result($subjectCode);
                    while ($subjectStatement->fetch()) {
                        $subjectCodes[] = $subjectCode;
                    }
                    $title = implode(', ', $subjectCodes);

                    $startDateTime = new DateTime($currentDate->format('Y-m-d') . ' ' . $time_from);
                    $endDateTime = new DateTime($currentDate->format('Y-m-d') . ' ' . $time_to);

                    $data[] = array(
                        'id' => $sched_id,
                        'title' => $title,
                        'subject' => $subject,
                        'start' => $startDateTime->format('Y-m-d H:i:s'),
                        'end' => $endDateTime->format('Y-m-d H:i:s'),
                        'repeating' => 'Weekly Schedule',
                        'faculty' => $prefix." ".$fname." ".substr($mname, 0, 1).". ".$lname,
                        'room' => $room
                    );
                }
                $currentDate->modify('+1 day');
            }
        } elseif ($sched_date) {
            $subjectCodes = array();
            $subjectQuery = "SELECT DISTINCT subject_code FROM subjects WHERE subject_description = ?";
            $subjectStatement = $con->prepare($subjectQuery);
            $subjectStatement->bind_param('s', $subject);
            $subjectStatement->execute();
            $subjectStatement->bind_result($subjectCode);
            while ($subjectStatement->fetch()) {
                $subjectCodes[] = $subjectCode;
            }
            $title = implode(', ', $subjectCodes);

            $startDateTime = new DateTime($sched_date . ' ' . $time_from);
            $endDateTime = new DateTime($sched_date . ' ' . $time_to);

            $data[] = array(
                'id' => $sched_id,
                'title' => $title,
                'start' => $startDateTime->format('Y-m-d H:i:s'),
                'end' => $endDateTime->format('Y-m-d H:i:s'),
                'repeating' => '',
                'faculty' => $prefix." ".$fname." ".substr($mname, 0, 1).". ".$lname,
                'room' => $room
            );
        }
    }
}

echo json_encode($data);
?>
