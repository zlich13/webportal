<?php 
session_start();
error_reporting(0);

//navigation styles
$mssstyle = "active";

// Connect to database
include '../../dbconnection.php';

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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ACTS Web Portal | Student Subjects</title>
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
   <!-- modal -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</head>
<body>

<?php
include 'NavigationBar.php';

$sem_id = $sy_ret['sem_id'];
$sy_id = $sy_ret['sy_id'];
$semester = $sy_ret['semester'];

function showTable($status, $sem, $sy) {
    global $table_contents, $con;
    $table_contents = "";

    $sql = "SELECT a.fname, a.mname, a.lname, s.student_num, s.student_year, s.student_course, c.acronym, sec.section_id, sec.name FROM applications a LEFT OUTER JOIN student_list s ON a.user_id=s.user_id LEFT OUTER JOIN course_strand c ON s.student_course=c.id LEFT OUTER JOIN student_section ssec ON s.student_num=ssec.student_num LEFT OUTER JOIN sections sec ON ssec.section_id=sec.section_id WHERE a.category = 2 AND s.enrolled_status = 1 ";
    if ($status == 1) {
    	$sql .= " AND NOT EXISTS (SELECT 1 FROM student_subjects ss WHERE ss.student_num = s.student_num AND ss.school_year_id = '$sy' AND ss.sem_id = '$sem')";
    } else if ($status == 2) {
    	$sql .= " AND EXISTS (SELECT 1 FROM student_subjects ss WHERE ss.student_num = s.student_num AND ss.school_year_id = '$sy' AND ss.sem_id = '$sem')";
    }

    $result = $con->query($sql);
    while ($row = $result->fetch_assoc()) {
        $student_course = $row["student_course"];
        $student_year = $row["student_year"];
        $student_num = $row["student_num"];
        $student_sec = $row["section_id"];

        // Generate edit button and set data-username attribute
        $edit_button = '<button type="button" class="edit-button btn btn-success" data-num="'.$student_num.'" data-id="'.$student_course.'" data-year="'.$student_year.'" data-sec="'.$student_sec.'" data-bs-toggle="modal" data-bs-target="#SubjectsList"><i class="las la-edit"></i></button>';
        $edit_button = str_replace(["\r", "\n"], "", $edit_button); // remove new lines

        $table_contents .= '<tr>
            					<td>'.$row["student_num"].'</td>
            					<td>'.$row["lname"].', '.$row["fname"].' '.$row["mname"].'</td>
            					<td>'.$row["acronym"].' - '.$row["student_year"].$row["name"].'</td>
            					<td>'.$edit_button.'</td>
        					</tr>';
    }
}

$stat = $_GET['sta'];
showTable($stat, $sem_id, $sy_id);

if(isset($_POST['status'])){
    $status = $_POST['status'];
    showTable($status, $sem_id, $sy_id);
}

?>
<main>
		<div>
	 		 <h5 class="title" id="main-title">Manage Subjects:</h5>
		</div>
	  
	<form method="post" id="filters">
	  <div id="form_div">
	  	<div class="filter-div">
	    <label for="status">Status:</label>
	    <select name="status" id="status" onchange="this.form.submit()">
	      <option value="1" <?php if(!isset($_POST['status']) || $_POST['status'] == 1) echo 'selected'; ?>>Pending</option>
	      <option value="2" <?php if(isset($_POST['status']) && $_POST['status'] == 2) echo 'selected'; ?>>Assigned</option>
	      <option <?php if(isset($_POST['status']) && $_POST['status'] == 'All') echo 'selected'; ?>>All</option>
	    </select>
	    </div>
	  </div>
	</form>

	<div id="tables_div">
	  <table id="tbl" class="table table-hover">
	    <thead class="table-success">
	      <tr>
	        <th>Student Number</th>
	        <th>Name</th>
	        <th>Year & Section</th>
	        <th></th>
	      </tr>
	    </thead>
	    <tbody>
	      <?php echo $table_contents; ?>
	    </tbody>
	  </table>
	</div>

	<div class="modal" id="SubjectsList" tabindex="-1" aria-labelledby="SubjectsList" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Choose Subjects to Enroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" style="overflow: auto;">
                <table id="modal-table" class="table">
                	<thead class="table-success">
                		<tr>
                			<th>Code</th>
                			<th>Description</th>
                			<th>Units</th>
                			<th>LAB</th>
                			<th></th>
                		</tr>
                	</thead>
                	<tbody></tbody>
                </table>
              </div>
              <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button id="enroll" name="enroll" type="button" class="btn btn-success">Enroll</button>
               </div>
          </div>
        </div> 
    </div> 
</main>
<script type="text/javascript">
$(document).ready(function() {
	const semester = <?php echo $semester ?>;
	const sem_id = <?php echo $sem_id ?>;
	const sy_id = <?php echo $sy_id ?>;
	//dataTable with buttons
	$('#tbl').DataTable({
		"columnDefs": [{
			"orderable": false,
			"targets": [3]
		}],
		dom: '<"top"<"layer1"f><"layer1"<"my-custom-element">><"layer2"lB>>t<"bottom"i>p<"clear">',
		buttons: [
			'copy', 'csv', 'excel', 'pdf', 'print'
		],
		initComplete: function() {
			$('.my-custom-element').prepend($('#filters'));
		}
	});
	// Attach the click event listener to the table and handle clicks on the edit button
			$('table').on('click', '.edit-button', function() {
					const student_num = $(this).data('num');
			    const course_id = $(this).data('id');
			    const year = $(this).data('year');
			    const sec = $(this).data('sec');
			    const selectedSubjects = []; // Array to store selected subjects
			    $.ajax({
						url: 'get_subjects.php',
						method: 'POST',
						data: {course_id: course_id, sem: semester, year: year, student_num: student_num},
						dataType: 'json',
						success: function(response) {
						    if (response.status === 'success') {
						      	const data = response.data
						      	const tableBody = $('#modal-table tbody');
				                // Clear the existing table rows
				                tableBody.empty();
				                
				                // Iterate over the data and append rows to the table
												data.forEach(function(subject) {
												    const row = $('<tr>');
												    row.append($('<td>').text(subject.subject_code));
												    row.append($('<td>').text(subject.subject_description));
												    row.append($('<td>').text(subject.units));
												    row.append($('<td>').text(subject.has_lab));
												    const checkboxCell = $('<td>');
												    const checkbox = $('<input type="checkbox">');
												    checkbox.attr('data-id', subject.subject_id);

												    // Check the checkbox if the subject is taken by the student
												    if (subject.is_taken) {
												        checkbox.prop('checked', true);
												    }
												    checkboxCell.append(checkbox);
												    row.append(checkboxCell);
												    tableBody.append(row);
												});
										    // Iterate over the checkboxes and check the initially checked ones
												$('#modal-table tbody input[type="checkbox"]').each(function() {
												    const isChecked = $(this).is(':checked');
												    const subjectId = $(this).attr('data-id');

												    if (isChecked) {
												        selectedSubjects.push(subjectId);
												    }
												    console.log(selectedSubjects);

												});
							} else {
								alert(response.message);
							}
						},
						error: function(xhr, status, error) {
						    console.error(xhr);
						    console.error(status);
						    console.error(error);
						}
				});

			   	$('#modal-table tbody').on('change', 'input[type="checkbox"]', function() {
				    const isChecked = $(this).is(':checked');
				    const subjectId = $(this).attr('data-id');

				    if (isChecked) {
				        selectedSubjects.push(subjectId);
				    } else {
				        const index = selectedSubjects.indexOf(subjectId);
				        if (index > -1) {
				            selectedSubjects.splice(index, 1);
				        }
				    }

			   	 console.log(selectedSubjects);
					});

			   	// Save button click event
			    $('#enroll').on('click', function() {
			        // Send the selected subjects array to the server-side script
			        $.ajax({
			            url: 'enroll_subjects.php',
			            method: 'POST',
			            data: { student_num: student_num, selectedSubjects: selectedSubjects, section: sec, sem_id: sem_id, sy_id: sy_id },
			            dataType: 'json',
			            success: function(response) {
			                // Handle the response from the server
			                if (response.status === 'success') {
			                    alert(response.message);
			                    document.location ='SubjectsManager.php?sta=1';
			                } else {
			                    alert(response.message);
			                }
			            },
			            error: function(xhr, status, error) {
			                // Handle the AJAX request error
			                console.error(xhr);
			                console.error(status);
			                console.error(error);
			            }
			        });
			    });
			});
});
</script>
</body>
</html>
<style type="text/css">
	.filter-div{
		width: 100%;
	}
</style>