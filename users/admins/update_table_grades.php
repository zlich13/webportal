<?php
// Include database connection
include('../../dbconnection.php');

// Get the ID of the schedule
$sched_id = $_GET['id'];

// Fetch updated data from the database based on the schedule ID
// Assuming you have executed your SQL query to fetch the updated data and stored the result in $result

// Generate HTML for the updated table rows
$table_contents = '';
$counter = 1;
// Assuming you're using MySQLi and have a $result variable containing the result set
while ($row = mysqli_fetch_assoc($result)) {
    $table_contents .= "<tr>
                            <td>$counter</td>
                            <td>{$row['student_name']}</td>
                            <td>{$row['prelim']}</td>
                            <td>{$row['midterm']}</td>
                            <td>{$row['prefinal']}</td>
                            <td>{$row['final']}</td>
                            <td>{$row['final_grade']}</td>
                            <td>{$row['scale']}</td>
                            <td>{$row['remarks']}</td>
                            <td>
                                <button type='button' class='btn btn-primary edit-button' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['student_num']}'>Edit</button>
                            </td>
                        </tr>";
    $counter++;
}

// Return the generated HTML
echo $table_contents;
?>