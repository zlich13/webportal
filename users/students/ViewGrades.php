<?php 
session_start();
error_reporting(0);

//navigation styles
$grdstyle = "active";

//connect to database
include('../../dbconnection.php');

// Redirect to logout page if no user logged in
if (empty($_SESSION['uid'])) {
    header('Location: ../../logout.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | View Grades </title>
	<!-- bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<!-- styles -->
  	<link rel="stylesheet" href="../../css/navigation.css"/>
  	<link rel="stylesheet" href="../../css/style.css"/>
  	<link rel="stylesheet" href="../../css/DataTables.css"/>
  	<!-- icons -->
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
	<!-- dataTable styles -->
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css">
	<!-- dataTable script -->
	<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>
  	<!--window icon-->
   	<link rel="shortcut icon" href="../../images/actsicon.png"/>
</head>

<body>

<?php

$uid = $_SESSION['uid'];
$stmt_student = $con->prepare("SELECT student_num FROM student_list WHERE user_id = ?");
$stmt_student->bind_param("i", $uid);
$stmt_student->execute();
$stmt_student->bind_result($student_num);
$stmt_student->fetch();
$stmt_student->close();


include('NavigationBar.php');

$sy_id = $sy_ret['sy_id'];
$sem_id = $sy_ret['sem_id'];
$s_year = $ret['student_year'];
$sem = $sy_ret['semester'];

function showTable($con, $student_num, $sem_id, $sy_id, &$table_contents, $sem, $sy)
{
    $table_contents = ''; // Initialize the variable
	
	
 $sql = "SELECT ss.subject_id, s.*, cs.subject, g.prelim, g.midterm, g.prefinal, g.final, g.final_grade, g.scale, g.remarks 
FROM student_subjects ss 
JOIN subjects s ON ss.subject_id = s.subject_id 
LEFT JOIN class_schedule cs ON s.subject_description = cs.subject
LEFT JOIN (
    SELECT student_num, cs_id, MAX(id) AS max_id
    FROM grades
    GROUP BY student_num, cs_id
) AS max_grades ON ss.student_num = max_grades.student_num AND cs.sched_id = max_grades.cs_id
LEFT JOIN grades g ON max_grades.student_num = g.student_num AND max_grades.cs_id = g.cs_id AND max_grades.max_id = g.id
WHERE ss.student_num = ? 
AND ss.sem_id = ?
AND ss.school_year_id = ?
ORDER BY g.final_grade IS NOT NULL DESC";


    $stmt = $con->prepare($sql);
    $stmt->bind_param("iii", $student_num, $sem_id, $sy_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $current_value = null;
        $counter = 1;
        while ($row = $result->fetch_assoc()) :
            $table_contents .= "<tr>
                                  <td>" . $row['subject_code'] . "</td>
                        <td>" . $row["subject_description"] . "</td>
                        <td class='" . ($row["prelim"] ? "" : "na-cell") . "'>" . ($row["prelim"] ?? "N/A") . "</td>
                        <td class='" . ($row["midterm"] ? "" : "na-cell") . "'>" . ($row["midterm"] ?? "N/A") . "</td>
                        <td class='" . ($row["prefinal"] ? "" : "na-cell") . "'>" . ($row["prefinal"] ?? "N/A") . "</td>
                        <td class='" . ($row["final"] ? "" : "na-cell") . "'>" . ($row["final"] ?? "N/A") . "</td>
                        <td class='" . ($row["final_grade"] ? "" : "na-cell") . "'>" . ($row["final_grade"] ?? "N/A") . "</td>
                        <td class='" . ($row["scale"] ? "" : "na-cell") . "'>" . ($row["scale"] ?? "N/A") . "</td>
                        <td class='" . ($row["remarks"] ? "" : "na-cell") . "'>" . ($row["remarks"] ?? "N/A") . "</td>
                                </tr>";
            $counter++;
        endwhile;
    }
}

// Usage
showTable($con, $student_num, $sem_id, $sy_id, $table_contents, $sem, $sy_id);

?>

<div id="tables_div">
    <table id="tbl"  class="table my-table">
        <thead class="calibri table-success">
            <tr>
                <th>COURSE CODE</th>
                <th>DESCRIPTION</th>
                <th>PRELIM</th>
                <th>MIDTERM</th>
                <th>PREFINAL</th>
                <th>FINAL</th>
                <th>FINAL GRADE</th>
                <th>SCALE</th>
                <th>REMARKS</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $table_contents; ?>
        </tbody>
    </table>
</div>

           </body>
        
		<script type="text/javascript">
			$(document).ready(function() {

				$('#tbl').DataTable({
					"columnDefs": [{
					"orderable": false,
				}],
					dom: '<"top"<"layer1"f><"layer1"<"my-custom-element">><"layer2"lB>>t<"bottom"i>p<"clear">',
					buttons: [
						'copy', 'csv', 'excel', 'pdf', 'print'
					],
					initComplete: function() {
					    $('.my-custom-element').prepend($('#filters'));
					}
				});

				// Handle clicks on the view button in the table
				$('.table').on('click', '.view-button', function() {
				window.location = $(this).data("href");
				});
			});
		</script>
	</main>
</body>
</html>
<style type="text/css">
	.filter-div{
		width: 100%;
	}
	.btn-div{
		text-align: right;
	}
	 .na-cell {
        background-color: #FFCCCC; /* Light red background */
        color: #FF6666; /* Dark red text color */
        padding: 5px; /* Padding around text */
    }

</style>
