<?php 
// Connect to the database
include '../../dbconnection.php';

$sy_query = mysqli_query($con, "SELECT sy.*, sem.* FROM school_years sy JOIN semesters sem ON sy.sy_id=sem.sy_id WHERE sy.is_active = 1 AND sem.sem_is_active = 1;");
$sy_ret = mysqli_fetch_array($sy_query);

$sy_active = $sy_ret['sy_id'];
$sem_active = $sy_ret['sem_id'];

        if (isset($_POST['sched_subject'])) {
            $sched_subject = $_POST['sched_subject'];
            $sql = "SELECT COUNT(ss.student_num) AS students_count, sec.section_id, sec.name, sec.year, c.acronym, sub.subject_description FROM student_subjects ss LEFT OUTER JOIN sections sec ON ss.section_id=sec.section_id LEFT OUTER JOIN course_strand c ON sec.course = c.id LEFT OUTER JOIN subjects sub ON ss.subject_id=sub.subject_id WHERE sub.subject_description = ? AND ss.school_year_id = ? AND ss.sem_id = ? GROUP BY ss.section_id ORDER BY sec.year ASC, c.acronym ASC, sec.name ASC;";
            $stmt = $con->prepare($sql);
            $stmt->bind_param('sii', $sched_subject, $sy_active, $sem_active);
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'No subject set'
            );
        }


    $stmt->execute();

    // Fetch the result
    $response = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'section_id' => $row['section_id'],
            'student_count' => $row['students_count'],
            'acronym' => $row['acronym'],
            'year' => $row['year'],
            'name' => $row['name']
        ];
    }

// Return the data as a JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
