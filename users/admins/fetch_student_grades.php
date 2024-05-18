<?php
// Include database connection or any necessary includes
include('../../dbconnection.php');

// Assume $_POST['studentId'] contains the student ID sent via AJAX
$gradeId = $_POST['gradeId'];
$studentId = $_POST['studId'];
$currentCsid = $_POST['currentCsId'];

// Fetch data from the database based on $studentId and current cs_id
$sql = "SELECT CONCAT(app.lname,', ',app.fname,' ',LEFT(app.mname, 1)) AS student_name, g.prelim, g.midterm, g.prefinal, g.final, g.final_grade, g.scale, g.remarks, g.student_num 
        FROM applications app 
        JOIN student_list s ON app.user_id = s.user_id
        JOIN grades g ON s.student_num = g.student_num
        JOIN class_schedule cs ON cs.sched_id = g.cs_id 
        JOIN subjects sub ON cs.subject = sub.subject_description 
        JOIN class_schedule_sections css ON cs.sched_id = css.class_id 
        JOIN sections sec ON css.section_id = sec.section_id 
        JOIN course_strand c ON sec.course = c.id 
        WHERE g.id= ? AND cs.sched_id = ?";

// Assume you have a variable $current_cs_id containing the current class panel ID
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $gradeId, $currentCsid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Encode the data as JSON and echo it back to the AJAX request
    echo json_encode($row);
} else {
    echo json_encode(array()); // Return empty JSON if no data found
}
?>