<?php
include('../../dbconnection.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gradeId = $_POST['gradeId'];
     $studId = $_POST['studId'];
    $editPrelim = $_POST['editPrelim'];
    $editMidterm = $_POST['editMidterm'];
    $editPrefinal = $_POST['editPrefinal'];
    $editFinal = $_POST['editFinal'];
    $editFinalGrade = $_POST['editFinalGrade'];
    $editScale = $_POST['editScale'];
    $editRemarks = $_POST['editRemarks'];
    $currentCsId = $_POST['currentCsId'];

    // Check if the student already has a grade for this class
    $checkSql = "SELECT * FROM grades WHERE cs_id = ? AND student_num = ?";
    $checkStmt = $con->prepare($checkSql);
    $checkStmt->bind_param("ii", $currentCsId, $studId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    $response['debug']['checkResult'] = $checkResult->num_rows;
        $response['debug']['studId'] = $studId;

    if ($checkResult->num_rows > 0) {
        // Update existing row for the student's grade
        $updateSql = "UPDATE grades SET prelim=?, midterm=?, prefinal=?, final=?, final_grade=?, scale=?, remarks=? WHERE cs_id=? AND id=?";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->bind_param("iiiiidsii", $editPrelim, $editMidterm, $editPrefinal, $editFinal, $editFinalGrade, $editScale, $editRemarks, $currentCsId, $gradeId);

        if ($updateStmt->execute()) {
            echo "Data updated successfully";
        } else {
            echo "Error updating data: " . $updateStmt->error;
    
        }
        $updateStmt->close();
    } else {
        // Insert new row for the student's grade
        $insertSql = "INSERT INTO grades (cs_id, student_num, prelim, midterm, prefinal, final, final_grade, scale, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $con->prepare($insertSql);
        $insertStmt->bind_param("iiiiiiids", $currentCsId, $studId, $editPrelim, $editMidterm, $editPrefinal, $editFinal, $editFinalGrade, $editScale, $editRemarks);

        if ($insertStmt->execute()) {
            echo "New row inserted for student's grade";
        } else {
            echo "Error inserting new row: " . $insertStmt->error;
        }
        $insertStmt->close();
    }
} else {
    echo "Invalid request method";
}
?>
