<?php 
session_start();
error_reporting(0);

//navigation styles
$dmstyle = "active";

// Connect to database
include '../../dbconnection.php';

// Redirect to logout page if no user logged in
if (empty($_SESSION['uid'])) {
    header('Location: ../../logout.php');
    exit;
}

// Include PhpSpreadsheet classes
require_once '../../PhpSpreadsheet-1.28.0/src/PhpSpreadsheet/IOFactory.php';
require '../../PhpSpreadsheet-1.28.0/vendor/autoload.php';

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | Subjects</title>
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

 	function showTable($year, $course){
 		global $table_contents, $con;
 		$table_contents = "";
 		$where_conditions = array();

 		if ($year !== 'All') {
	 			// if year not all, add where condition
	        	$where_conditions[] = "s.sub_grade_year = $year";
	    	}

		if ($course !== 'All') {
		 // if course not all, add where condition
		    $where_conditions[] = "s.sub_course = $course";
		}

		if (!empty($where_conditions)) {
		    	// add where conditions
 			$sql= "SELECT s.*, c.acronym FROM subjects s LEFT OUTER JOIN course_strand c  ON s.sub_course=c.id WHERE " . implode(' AND ', $where_conditions);
 		} else {
 			$sql= "SELECT s.*, c.acronym FROM subjects s LEFT OUTER JOIN course_strand c  ON s.sub_course=c.id";
 		}

 		// execute query and generate table contents
 		$result=$con->query($sql);
		while ($row = $result->fetch_assoc()) {
    		$edit_button = '<button type="button" class="edit-button btn btn-success" data-id="'.$row["subject_id"].'"data-bs-toggle="modal" data-bs-target="#myModal"><i class="las la-edit"></i></button>';
    		$edit_button = str_replace("\r\n", "", $edit_button); // remove new lines

    		$delete_button = '<button type="button" class="delete-button btn btn-danger" data-code="'.$row["subject_code"].'" ><i class="las la-trash"></i></button>';
    		$delete_button = str_replace("\r\n", "", $delete_button); // remove new lines

 				$table_contents .= '<tr>
				<td>' .$row["subject_code"] .'</td>
				<td>' .$row["subject_description"] .'</td>
				<td>' .$row["sub_grade_year"] .'</td>
				<td>' .$row["acronym"] .'</td>
				<td>' .$row["units"] .'</td>
				<td>' .$row["semester"] .'</td>
				<td>' .$row["has_lab"] .'</td>
				<td>' .$edit_button.$delete_button. '</td>
			</tr>';
		} 
 	}

 	showTable('All',"All");

 	if (isset($_POST['year'])||isset($_POST['course'])) {
 		$year=$_POST['year'];
 		$course=$_POST['course'];
 		showTable($year,$course);;
 	}

// Check if a file was uploaded
if(isset($_FILES['file-upload']) && $_FILES['file-upload']['error'] === UPLOAD_ERR_OK) {
    
    // Get file name and path
    $inputFileName = $_FILES['file-upload']['tmp_name'];
    
    try {
        // Read the spreadsheet
        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        
        // Get the first worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Get the highest row and column numbers used in the worksheet
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        // Loop through each row of the worksheet
		for ($row = 2; $row <= $highestRow; $row++) {
		    
		    // Read a row of data into an array
		    $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
		    
		    // Get the values from the array
		    $row_code = $rowData[0][0];
		    $row_desc = $rowData[0][1];
		    $row_year = $rowData[0][2];
		    $row_course = $rowData[0][3];
		    $row_units = $rowData[0][4];
		    $row_semester = $rowData[0][5];
		    $row_lab = $rowData[0][6];

		    // Get the course id from the database
		    $sql = "SELECT id FROM course_strand WHERE acronym = '$row_course'";
		    $result = mysqli_query($con, $sql);
		    if (mysqli_num_rows($result) == 0) {
		        $course_id = null;
		    } else {
		        $ret = mysqli_fetch_assoc($result);
		        $course_id = $ret['id'];
		    }
		    
			    // Insert the data into the database
			    $stmt = $con->prepare("INSERT INTO subjects (subject_description, subject_code, sub_grade_year, sub_course, units, semester, has_lab) VALUES (?, ?, ?, ?, ?, ?, ?)");
				$stmt->bind_param("ssiiiii", $row_desc, $row_code, $row_year, $course_id, $row_units, $row_semester, $row_lab);

				// Execute the statement and check for errors
				if (!$stmt->execute()) {
				    echo "<script>alert('Error: " . $stmt->error . "');</script>";
				}
		}
    } catch (Exception $e) {
        echo 'Error loading file: ',  $e->getMessage(), "\n";
    }

		echo "<script type='text/javascript'>document.location ='Subjects.php';</script>";
}

if (isset($_POST['add'])) {
  $add_year = $_POST['modal-year'];
  $add_course = $_POST['modal-course'];

  // Fetch the active semester from the semesters table
  $stmt = $con->prepare("SELECT semester FROM semesters WHERE sem_is_active = 1");
  $stmt->execute();
  $stmt->bind_result($active_semester);
  $stmt->fetch();
  $stmt->close();

  if (!empty($active_semester)) {
    // Prepare and bind statement for adding subjects
    $stmt = $con->prepare("INSERT INTO subjects (subject_description, subject_code, sub_grade_year, sub_course, units, has_lab, semester) VALUES (?,?,?,?,?,?,?)");

    // Loop through the subject fields and execute the insert statement
    for ($i = 0; $i < count($_POST['modal-desc']); $i++) {
      $add_desc = $_POST['modal-desc'][$i];
      $add_code = $_POST['modal-code'][$i];
      $add_units = $_POST['modal-units'][$i];
      $add_laboratory = ($_POST['modal-laboratory'][$i] === "Yes") ? 1 : 0;

      // Execute the statement for each set of subject data
      $stmt->bind_param("ssiiiii", $add_desc, $add_code, $add_year, $add_course, $add_units, $add_laboratory, $active_semester);

      // Execute the statement and check for errors
      if (!$stmt->execute()) {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
        break; // Break the loop if an error occurs
      }

      // Reset the parameters for the next iteration
      $stmt->reset();
    }

    // Close the statement
    mysqli_stmt_close($stmt);

    // Redirect to the Subjects page
    echo "<script type='text/javascript'>document.location ='Subjects.php';</script>";
  } else {
    echo "<script>alert('No active semester found.'); </script>";
  }
}
        
if (isset($_POST['update'])) {
  $edit_id = $_POST['edit-id'];


  $query = "SELECT * FROM subjects WHERE subject_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $ret = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    $old_desc = $ret['subject_description'];
    $old_code = $ret['subject_code'];
    $old_year = $ret['sub_grade_year'];
    $old_course = $ret['sub_course'];
    $old_units = $ret['units'];
    $old_lab = $ret['has_lab'];

	$edit_desc = $_POST['modal-desc'][0];
	$edit_code = $_POST['modal-code'][0];
	$edit_year = $_POST['modal-year'];
	$edit_course = $_POST['modal-course'];
	$edit_units = $_POST['modal-units'][0];
	// Convert "Yes" and "No" to 1 and 0
    $edit_lab = ($_POST['modal-laboratory'][0] === "Yes") ? 1 : 0;
	

	// Disable autocommit
	mysqli_autocommit($con, false);
	$error_occurred = false;

	if ($old_desc != $edit_desc || $old_code != $edit_code || $old_year != $edit_year || $old_course != $edit_course || $old_units != $edit_units || $old_lab != $edit_lab) {
		// Start transaction
    	mysqli_begin_transaction($con);

    	// Update description
	    if ($old_desc != $edit_desc) {
	        $sql = "UPDATE subjects SET subject_description=? WHERE subject_id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "si", ucwords($edit_desc), $edit_id);
	        if (!mysqli_stmt_execute($stmt)) {
	            // Rollback transaction and display an error message if the update fails
	            mysqli_rollback($con);
	            echo "<script>alert('Error updating subject description: " . mysqli_error($con) . "');</script>";
	            exit;
	        }
	        mysqli_stmt_close($stmt);
	    }

	    // Update code
	    if ($old_code != $edit_code) {
	        // Update the code
	        $sql = "UPDATE subjects SET subject_code=? WHERE subject_id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "si", strtoupper($edit_code), $edit_id);
	        if (!mysqli_stmt_execute($stmt)) {
	            // Rollback transaction and display an error message if the update fails
	            mysqli_rollback($con);
	            echo "<script>alert('Error updating subject code: " . mysqli_error($con) . "');</script>";
	            exit;
	        }
	        mysqli_stmt_close($stmt);
	    }

	   	// Update year
	    if ($old_year != $edit_year) {
	        $sql = "UPDATE subjects SET sub_grade_year=? WHERE subject_id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "ii", $edit_year, $edit_id);
	        if (!mysqli_stmt_execute($stmt)) {
	            // Rollback transaction and display an error message if the update fails
	            mysqli_rollback($con);
	            echo "<script>alert('Error updating subject year: " . mysqli_error($con) . "');</script>";
	            exit;
	        }
	        mysqli_stmt_close($stmt);
	    }

	    // Update course
	    if ($old_course != $edit_course) {
	        $sql = "UPDATE subjects SET sub_course=? WHERE subject_id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "ii", $edit_course, $edit_id);
	        if (!mysqli_stmt_execute($stmt)) {
	            // Rollback transaction and display an error message if the update fails
	            mysqli_rollback($con);
	            echo "<script>alert('Error updating subject course: " . mysqli_error($con) . "');</script>";
	            exit;
	        }
	        mysqli_stmt_close($stmt);
	    }

		// Update units
	    if ($old_units != $edit_units) {
	        $sql = "UPDATE subjects SET units=? WHERE subject_id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "ii", $edit_units, $edit_id);
	        if (!mysqli_stmt_execute($stmt)) {
	            // Rollback transaction and display an error message if the update fails
	            mysqli_rollback($con);
	            echo "<script>alert('Error updating subject units: " . mysqli_error($con) . "');</script>";
	            exit;
	        }
	        mysqli_stmt_close($stmt);
		}
		// Update lab
	    if ($old_lab != $edit_lab) {
	        $sql = "UPDATE subjects SET has_lab=? WHERE subject_id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "ii", $edit_lab, $edit_id);
	        if (!mysqli_stmt_execute($stmt)) {
	            // Rollback transaction and display an error message if the update fails
	            mysqli_rollback($con);
	            echo "<script>alert('Error updating subject course: " . mysqli_error($con) . "');</script>";
	            exit;
	        }
	        mysqli_stmt_close($stmt);
	    }

		// Commit transaction
		mysqli_commit($con);

		// Close database connection
		mysqli_close($con);

		// Redirect to the Subjects page
		echo "<script>document.location = 'Subjects.php';</script>";
	}
}
?>
	<main>
		<div  class="title-div flex gap">
	     	<div>
	        	<h5 class="title" id="main-title">Subjects:</h5>  
	      	</div>
			<div style="display: inline-flex; gap: 5px;">
				<div class="d-flex flex-column flex-sm-row gap-3">
  <button class="btn btn-success" type="submit" id="upload-button" name="upload-button" data-bs-toggle="modal" data-bs-target="#bulkModal">
    <span class="las la-upload" style="margin-right: 3px;"></span>Upload Subjects
  </button>
  <button class="btn btn-success" type="submit" id="add-button" name="add-button" data-bs-toggle="modal" data-bs-target="#myModal">
    <span class="las la-plus" style="margin-right: 3px;"></span>New Subject
  </button>
</div>
			</div>
    	</div>

				
      	<form method="post" id="filters">
      			<div id="form_div">
      				<div class="filter-div">	
				      	<label>Year: </label>
      					<select name="year" id="year" onchange="this.form.submit()">
      						<option <?php if(isset($_POST['year']) && $_POST['year'] == 'All') echo 'selected';?>>All</option>
      						<option <?php if(isset($_POST['year']) && $_POST['year'] == 11) echo 'selected'; ?>>11</option>
                			<option <?php if(isset($_POST['year']) && $_POST['year'] == 12) echo 'selected'; ?>>12</option>
                			<option <?php if(isset($_POST['year']) && $_POST['year'] == 1) echo 'selected'; ?>>1</option>
                			<option <?php if(isset($_POST['year']) && $_POST['year'] == 2) echo 'selected'; ?>>2</option>
                			<option <?php if(isset($_POST['year']) && $_POST['year'] == 3) echo 'selected'; ?>>3</option>
                			<option <?php if(isset($_POST['year']) && $_POST['year'] == 4) echo 'selected'; ?>>4</option>
      					</select>
			      	</div>
			      	<div class="filter-div">	
				      	<label for="course">Strand/Course</label>
	                  	<select name='course' id="course" onchange="this.form.submit()">
	                    	<option <?php if(isset($_POST['course']) && $_POST['course'] == 'All') echo 'selected';?>>All</option>
	                      	<!-- get strands from database --> <?php 
		                    $query=mysqli_query($con,"SELECT * FROM course_strand WHERE type = 1");
		                    while($row=mysqli_fetch_array($query)){ ?>    
		                      <option class = "strandOps" value="<?php echo $row['id'];?>" <?php if(isset($_POST['course']) && $_POST['course'] == $row['id']) echo 'selected';?>> <?php echo $row['acronym']?></option> <?php 
		                  	} 
		                    // get courses from database
		                    $query=mysqli_query($con,"SELECT * FROM course_strand WHERE type = 2");
		                    while($row=mysqli_fetch_array($query)){ ?>    
		                      <option class = "courseOps" value="<?php echo $row['id'];?>"  <?php if(isset($_POST['course']) && $_POST['course'] == $row['id']) echo 'selected';?>><?php echo $row['acronym']?></option> <?php 
		                  	} ?>  
	                  	</select>
			      	</div>
            	</div>
      	</form>
      	<div id="tables_div">
					<table id="tbl"  class="table table-hover">
						<thead class="table-success">
							<tr>
							<th>Code</th>
							<th>Description</th>
							<th>Year</th>
							<th>Course</th>
							<th>Units</th>
							<th>In Semester</th>
							<th>Laboratory</th>
							<th></th>
							</tr>
						</thead>
						<tbody>
							<?php
					  		echo "$table_contents";
							?>
						</tbody>
					</table>
				</div>
	<!-- Modal for New and Edit Accounts -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title for_add" id="myModalLabel">New Subject</h5>
        <h5 class="modal-title for_edit" id="myModalLabel">Edit Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="modal-form" method="post">
        <div class="modal-body">
          <input id="edit-id" name="edit-id" type="hidden" required>

          <div class="mb-3">
            <label for="modal-year" class="form-label">Year: </label>
            <select name="modal-year" id="modal-year" required onchange="filterOptions();">
              <option value="" hidden>Select</option>
              <option value="11">11</option>
              <option value="12">12</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="modal-course" class="form-label">Course/Strand: </label>
            <select name='modal-course' id="modal-course" required>
              <option value="" hidden>Select</option>
              <?php
              $query=mysqli_query($con,"SELECT * FROM course_strand WHERE type = 1");
              while($row=mysqli_fetch_array($query)){ ?>
                <option class="strandops" value="<?php echo $row['id'] ?>"> <?php echo $row['acronym'];?></option> <?php
              }
              $query=mysqli_query($con,"SELECT * FROM course_strand WHERE type = 2");
              while($row=mysqli_fetch_array($query)){ ?>
                <option class="courseops" value="<?php echo $row['id'] ?>"> <?php echo $row['acronym'];?></option>
              <?php } ?>
            </select>
          </div>
          
          <!-- Container to hold dynamic subject fields -->
          <div id="dynamic-subject-fields-container" style="margin-bottom: 20px;">
            <!-- Initial set of subject fields -->
            <div class="dynamic-subject-fields">
              <div class="subject-indicator">Subject 1</div>
              <div class="mb-3">
                <label for="modal-desc" class="form-label">Description: </label>
                <input class="subject-input" id="modal-desc" name="modal-desc[]" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="modal-code" class="form-label">Code: </label>
                <input class="subject-input" id="modal-code" name="modal-code[]" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="modal-units" class="form-label">Units: </label>
                <input class="subject-input" id="modal-units" name="modal-units[]" type="number" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="modal-laboratory" class="form-label">Has Laboratory?</label>
                <select class="subject-input" id="modal-laboratory" name="modal-laboratory[]" required>
                  <option value="" hidden>Select</option>
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </div>
            </div>
          </div>
          
          <!-- Button to add more subject fields -->
          <button type="button" class="btn btn-success for_anothersub" onclick="addSubjectFields()" style="margin-bottom: 10px;">Add Another Subject</button>
          
        </div>
        <div class="modal-footer">
          <button id="add" name="add" type="submit" class="btn btn-success for_add">Add</button>
          <button id="update" name="update" type="submit" class="btn btn-success for_edit">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="bulkModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
    	<div class="modal-content">
      		<div class="modal-header">
       		 	<h5 class="modal-title" id="myModalLabel">Upload Subjects</h5>
       			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      		</div>
	      	<div class="modal-body">
	      		<div style="display: flex; justify-content: space-between;">
		      		<div>
		      			<a href="../../templates/SubjectsTemplate.xlsx" download id="template"><span class="las la-download" style="margin-right: 3px"></span>SubjectsTemplate.xlsx</a>
		      		</div>
		      		<div>
			      		<form method="post" enctype="multipart/form-data">
				            <label for="file-upload" class="custom-file-upload">Choose File</label>
				            <input id="file-upload" name="file-upload" type="file" style="display: none;"/>
					    </form>
				    </div>
      			</div>
	      	</div>
	    </div>
	</div>
</div>
	</main>
	<script>
let subjectCounter = 1;

function addSubjectFields() {
  // Clone the original subject fields container
  var originalContainer = document.querySelector('.dynamic-subject-fields');
  var cloneContainer = originalContainer.cloneNode(true);

  // Clear values in the cloned subject fields
  var clonedInputs = cloneContainer.querySelectorAll('input[type="text"], input[type="number"]');
  clonedInputs.forEach(function (input) {
    input.value = '';
  });

  // Update the subject indicator
  var subjectIndicator = cloneContainer.querySelector('.subject-indicator');
  subjectIndicator.textContent = 'Subject ' + (++subjectCounter);

  // Append the cloned container to the parent
  originalContainer.parentNode.appendChild(cloneContainer);

  // Add remove button to the cloned subject field
  var removeButton = document.createElement('button');
  removeButton.type = 'button';
  removeButton.className = 'btn btn-danger for_remove';
  removeButton.textContent = 'Remove';
  removeButton.onclick = function () {
    cloneContainer.remove(); // Remove the cloned subject field
    subjectCounter--; // Decrement the subject counter
  };

  // Append the remove button to the cloned subject field
  cloneContainer.appendChild(removeButton);
}

// Event listener for modal close button
$('#myModal').on('hidden.bs.modal', function () {
  // Reset the form when the modal is closed
  $('#modal-form')[0].reset();
  var modal = document.getElementById('myModal');
  modal.style.display = 'none';
  clearAdditionalSubjects();
});

function clearAdditionalSubjects() {
  // Select all cloned subject fields except the first one
  var excessSubjects = document.querySelectorAll('.dynamic-subject-fields:not(:first-child)');

  // Remove each excess subject field
  excessSubjects.forEach(function (subject) {
    subject.remove();
  });

  // Reset subject counter
  subjectCounter = 1;
}

</script>

	<script type="text/javascript">
		filterOptions();
		filterOptions2();
		$(document).ready(function() {
			//dataTable with buttons
			$('#tbl').DataTable({
				"columnDefs": [{
					"orderable": false,
					"targets": [0,1,7]
				}],
				dom: '<"top"<"layer1"f><"layer1"<"my-custom-element">><"layer2"lB>>t<"bottom"i>p<"clear">',
				buttons: [
					'copy', 'csv', 'excel', 'pdf', 'print'
				],
				initComplete: function() {
					$('.my-custom-element').prepend($('#filters'));
				}
			});

			// Show the modal window when the add button is clicked
			$('#add-button').click(function() {
				$('#modal-desc').val("");
				$('#modal-code').val("");
			  	$('#modal-units').val("");
			  	$('#modal-year').prop('selectedIndex', 0);
			  	$('#modal-course').prop('selectedIndex', 0);
			  	$('#modal-laboratory').prop('selectedIndex', 0);
			  	$('.for_edit').hide();
				$('.for_add').show();
				$('.for_anothersub').show();
				$('#modal-form')[0].reset();
			});



			// Attach the click event listener to the table and handle clicks on the edit button
			$('table').on('click', '.edit-button', function() {
				$('.for_add').hide();
				$('.for_edit').show();
				$('.for_anothersub').hide();
			    var id = $(this).data('id');
			    $('#edit-id').val(id);

			    

			    $.ajax({
						url: 'EditSubjects.php',
						method: 'POST',
						data: {id: id},
						dataType: 'json',
						success: function(response) {
							
						    if (response.status === 'success') {
						        $('#modal-desc').val(response.data.desc);
						        $('#modal-code').val(response.data.code);
						       	$('#modal-year').val(response.data.year);
						       	$('#modal-year').val(response.data.year).prop('selected', true);
						       	filterOptions();
						       	$('#modal-course').val(response.data.course).prop('selected', true);
						        $('#modal-units').val(response.data.units);
						        $('#modal-laboratory').val(response.data.has_lab).prop('selected', true);
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
			});

			// Handle clicks on the delete button in the table
			$('.table').on('click', '.delete-button', function() {
				var code = $(this).data('code');
			  const option = confirm('Delete ' +  code + '?');
			  if (option) {
			    // execute the query
			    $.ajax({
			      url: 'DeleteSubjects.php',
			      type: 'post',
			      data: {code: code},
			      success: function(response) {
			        document.location = "Subjects.php";
			      },
			      error: function(xhr, status, error) {
			        alert("Error! Please try again later.");
			        console.log(xhr.responseText);
			      }
			    });
			  } else {
			    // do nothing
			  }
			});


			// Event listener for modal close button
      $('#myModal').on('hidden.bs.modal', function() {
        // Reset the form when the modal is closed
        $('#modal-form')[0].reset();
        var modal = document.getElementById('myModal');
  		modal.style.display = 'none';
  		clearAdditionalSubjects();
      });

      function clearAdditionalSubjects() {
  // Select all cloned subject fields except the first one
  var excessSubjects = document.querySelectorAll('.dynamic-subject-fields:not(:first-child)');

  // Remove each excess subject field
  excessSubjects.forEach(function (subject) {
    subject.remove();
  });

  // Reset subject counter
  subjectCounter = 1;
}



		});

//filter select options
function filterOptions() {
  const grade_year = document.getElementById("modal-year").value;
  const modal_course = document.getElementById("modal-course");
  let strandops = document.getElementsByClassName("strandops");
  let courseops = document.getElementsByClassName("courseops");
  
  if (grade_year == 11 || grade_year == 12) {
    showElements(strandops);
    hideElements(courseops);
    modal_course.selectedIndex = 0; // set selected index to 0
  } else if (grade_year >= 1 && grade_year < 5) {
    showElements(courseops);
    hideElements(strandops);
    modal_course.selectedIndex = 0; // set selected index to 0
  } else {
  	hideElements(strandops);
  	hideElements(courseops);
  	modal_course.selectedIndex = 0; // set selected index to 0
  }
}

function filterOptions2() {
  const year = document.getElementById("year").value;
  const course = document.getElementById("course");
  const strandOps = document.getElementsByClassName("strandOps");
  const courseOps = document.getElementsByClassName("courseOps");
  
  if (year == "All") {
    showElements(strandOps);
    showElements(courseOps);
  } else if (year == "11" || year == "12") {
    showElements(strandOps);
    hideElements(courseOps);
  } else {
    showElements(courseOps);
    hideElements(strandOps);
  }
}

function hideElements(elements) {
  for (let i = 0; i < elements.length; i++) {
    elements[i].style.display = "none";
  }
}

function showElements(elements) {
  for (let i = 0; i < elements.length; i++) {
    elements[i].style.display = "block";
  }
}

const fileInput = document.getElementById("file-upload");
	fileInput.addEventListener("change", (event) => {
    const file = event.target.files[0];
    if (!file || !file.type.match(/application\/vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet/)) {
      alert("Only Excel files (XLSX) are allowed!");
      fileInput.value = '';
      return;
    }
    // Auto-submit the file
    fileInput.form.submit();
});

</script>


</body>
</html>
<style type="text/css">
	.filter-div{
		width: 100%;
	}
	.custom-file-upload {
    display: inline-block;
    padding: 7px;
    background-color: #0a58ca;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-weight: normal;
    margin-right: 5px;
  }

  .custom-file-upload:hover {
    background-color: #0d6efd;;
  }

  #template{
  	color: var(--main-color);
  }
  #template:hover{
  	text-decoration: underline !important;
  }
 #subject-indicator {
        text-align: center;
    }

    .subject-input {
        margin-top: 10px;
    }

    .subject-indicator {
        font-weight: bold;
    }

    .hidden {
        display: none;
    }
    
@media (max-width: 576px) {
  .flex-column-sm-row {
    flex-direction: column; /* Change flex direction to column for small screens */
  }
}
.for_remove{
	font-size:14px;
}
</style>