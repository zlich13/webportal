<?php
// Connect to the database
include('../../dbconnection.php');

$response = array();

if (isset($_POST['student_num']) && isset($_POST['selectedSubjects']) && isset($_POST['section']) && isset($_POST['sem_id']) && isset($_POST['sy_id'])) {

    $student_num = $_POST['student_num'];
    $selectedSubjects = $_POST['selectedSubjects'];
    $section = $_POST['section'];
    $sem_id = $_POST['sem_id'];
    $sy_id = $_POST['sy_id'];

    // Retrieve the existing subject IDs for the student
    $existingSubjects = array();
    $sql = "SELECT subject_id FROM student_subjects WHERE student_num = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $student_num);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $existingSubjects[] = $row['subject_id'];
    }

    // Start a transaction
    $con->begin_transaction();

    // Prepare the SQL statements for updating, inserting, and deleting records
    $updateSql = "UPDATE student_subjects SET section_id = ?, sem_id = ?, school_year_id = ? WHERE student_num = ? AND subject_id = ?";
    $insertSql = "INSERT INTO student_subjects (student_num, subject_id, sem_id, section_id, school_year_id) VALUES (?, ?, ?, ?, ?)";
    $deleteSql = "DELETE FROM student_subjects WHERE student_num = ? AND subject_id = ?";

    $updateStmt = $con->prepare($updateSql);
    $insertStmt = $con->prepare($insertSql);
    $deleteStmt = $con->prepare($deleteSql);

    // Flag to track if any errors occur during the database operations
    $errorFlag = false;

    // Update, insert, or delete records based on selected subjects
    foreach ($selectedSubjects as $subjectId) {
        if (in_array($subjectId, $existingSubjects)) {
            // Update existing record
            $updateStmt->bind_param('iiiii', $section, $sem_id, $sy_id, $student_num, $subjectId);
            $result = $updateStmt->execute();
        } else {
            // Insert new record
            $insertStmt->bind_param('iiiii', $student_num, $subjectId, $sem_id, $section, $sy_id);
            $result = $insertStmt->execute();
        }

        if (!$result) {
            $errorFlag = true;
            break; // Stop the loop if an error occurs
        }
    }

    // Delete records for subjects that were not selected
    $deleteSubjects = array_diff($existingSubjects, $selectedSubjects);

    foreach ($deleteSubjects as $subjectId) {
        $deleteStmt->bind_param('ii', $student_num, $subjectId);
        $result = $deleteStmt->execute();

        if (!$result) {
            $errorFlag = true;
            break; // Stop the loop if an error occurs
        }
    }

    // Close the SQL statements
    $stmt->close();
    $updateStmt->close();
    $insertStmt->close();
    $deleteStmt->close();

    if ($errorFlag) {
        // Rollback the transaction if an error occurs
        $con->rollback();
        $response = array(
            'status' => 'error',
            'message' => 'Error updating subjects'
        );
    } else {
        // Commit the transaction if all operations are successful
        $con->commit();

        $response = array(
            'status' => 'success',
            'message' => 'Subjects updated successfully'
        );
    }

    // Close the database connection
    $con->close();
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Data incomplete'
    );
}

echo json_encode($response);
?>
