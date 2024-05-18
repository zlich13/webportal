<?php 
session_start();
error_reporting(0);

$dmstyle = "active";

// Connect to database
include '../../dbconnection.php';

// Redirect to logout page if no user logged in
if (empty($_SESSION['uid'])) {
    header('Location: ../../logout.php');
    exit;
}

require_once '../../PhpSpreadsheet-1.28.0/src/PhpSpreadsheet/IOFactory.php';
require '../../PhpSpreadsheet-1.28.0/vendor/autoload.php';

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | Courses</title>
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

 	function showTable($type){
 		global $table_contents, $con;
 		$table_contents = "";

 		$sql= "SELECT * FROM course_strand";
 		if ($type !== 'All') {
 				$sql = $sql." where type = $type";
 		}

 		$result=$con->query($sql);
		while ($row = $result->fetch_assoc()) {
			//convert type to text
			$type = ($row['type'] == 1) ? "Strand" : 
					      (($row['type'] == 2) ? "Course" : null);

			// Generate edit button and set data-username attribute
    		$edit_button = '<button type="button" class="edit-button btn btn-success" data-id="'.$row["id"].'" data-bs-toggle="modal" data-bs-target="#myModal"><i class="las la-edit"></i></button>';
    		$edit_button = str_replace("\r\n", "", $edit_button); // remove new lines

    		$delete_button = '<button type="button" class="delete-button btn btn-danger" data-acronym="'.$row["acronym"].'"><i class="las la-trash"></i></button>';
    		$delete_button = str_replace("\r\n", "", $delete_button); // remove new lines

 				$table_contents .= '<tr>
				<td>' .$row["name"] .'</td>
				<td>' .$row["acronym"] .'</td>
				<td>' .$type .'</td>
				<td>
				'.$edit_button.$delete_button.'</td>
			</tr>';
		} 
 	}

 	showTable('All');

 if(isset($_POST['type'])){
	$type = $_POST['type'];
	showTable($type);
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
		    $row_name = $rowData[0][0];
		    $row_acronym = $rowData[0][1];
		    $row_type = $rowData[0][2];
		    
		    $sql = "SELECT id FROM course_strand WHERE name='$row_name' OR acronym='$row_acronym'";
	    	$result = mysqli_query($con, $sql);

			if (mysqli_num_rows($result) == 0) {
			    // Insert the data into the database
			    $stmt = $con->prepare("INSERT INTO course_strand (name, acronym, type) VALUES (?, ?, ?)");
				$stmt->bind_param("ssi", $row_name, $row_acronym, $row_type);

				// Execute the statement and check for errors
				if (!$stmt->execute()) {
				    echo "<script>alert('Error: " . $stmt->error . "');</script>";
				}
			} else {
			    echo "<script>alert('Skipping course/strand with acronym = $row_acronym');</script>";
			}
		}
		echo "<script type='text/javascript'>document.location ='Courses.php';</script>";
    } catch (Exception $e) {
        echo 'Error loading file: ',  $e->getMessage(), "\n";
    }
}

if (isset($_POST['add'])) {
    $add_course = $_POST['modal-course'];
    $add_acronym = $_POST['modal-acronym'];
    $add_type = $_POST['modal-type'];

    // Check if the new course is unique
	    $sql = "SELECT id FROM course_strand WHERE name='$add_course' OR acronym='$add_acronym'";
	    $result = mysqli_query($con, $sql);
	    if ($result && mysqli_num_rows($result) > 0) {
	        echo "<script>alert('Record already exist.');</script>";
	    } else {
	    	// Prepare and bind statement
				$stmt = $con->prepare("INSERT INTO course_strand (name, acronym, type) VALUES (?,?,?)");
				$stmt->bind_param("sss", ucwords($add_course), strtoupper($add_acronym), $add_type);

			// Execute the statement
			if ($stmt->execute()) {
				echo "<script type='text/javascript'>document.location ='Courses.php';</script>";
			} else {
				echo "<script>alert('Error! Try again later.');</script>";
			}
				// close the statement
				mysqli_stmt_close($stmt);
			}
}
        
if (isset($_POST['update'])) {
  $edit_id = $_POST['edit-id'];
  $error_alert = "<script>alert('Error! Try again later.');</script>";

  $query = "SELECT name, acronym, type FROM course_strand WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $ret = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    $old_name = $ret['name'];
    $old_acronym = $ret['acronym'];
    $old_type = $ret['type'];

	  $edit_name = $_POST['modal-course'];
	  $edit_acronym = $_POST['modal-acronym'];
	  $edit_type = $_POST['modal-type'];

	  if ($old_name != $edit_name) {
		  	// Check if the new course is unique
		    $sql = "SELECT id FROM course_strand WHERE name='$edit_name'";
		    $result = mysqli_query($con, $sql);
		    if ($result && mysqli_num_rows($result) > 0) {
		        echo "<script>alert('Course/Strand name already exist.');</script>";
		    } else {
			$sql = "UPDATE course_strand SET name=? WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", ucwords($edit_name), $edit_id);
            if (mysqli_stmt_execute($stmt)) {
                // Username updated successfully
            } else {
                // Display an error message if the username update fails
                echo $error_alert;
            }
            mysqli_stmt_close($stmt);
				}
		}

		if ($old_acronym != $edit_acronym) {
				// Check if the new course acronym is unique
		    $sql = "SELECT id FROM course_strand WHERE acronym='$edit_acronym'";
		    $result = mysqli_query($con, $sql);
				if ($result && mysqli_num_rows($result) > 0) {
		        echo "<script>alert('Course/Strand acronym already exist.');</script>";
		    } else {
			 	$sql = "UPDATE course_strand SET acronym=UPPER(?) WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", strtoupper($edit_acronym), $edit_id);
            if (mysqli_stmt_execute($stmt)) {
                // Username updated successfully
            } else {
                // Display an error message if the username update fails
                echo $error_alert;
            }
            mysqli_stmt_close($stmt);
        }
		}

		if ($old_type != $edit_type) {
			 $sql = "UPDATE course_strand SET type=? WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", $edit_type, $edit_id);
            if (mysqli_stmt_execute($stmt)) {
                // type updated successfully
            } else {
                // Display an error message if the type update fails
                echo $error_alert;
            }
            mysqli_stmt_close($stmt);
		}
		//refresh
		echo "<script type='text/javascript'>document.location ='Courses.php';</script>";
}
?>
	<main>
		<div class="title-div flex gap">
     		<div>
        		<h5 class="title" id="main-title">Strands/Courses:</h5>  
      		</div>
      		<div style="display: inline-flex; gap: 5px;">
      			<div>
				  <button class="btn btn-success" type="submit" id= "upload-button" name="upload-button" data-bs-toggle="modal" data-bs-target="#bulkModal"><span class="las la-upload" style="margin-right: 3px;"></span>Upload Courses</button>
				</div>
		        <div>
				  <button class="btn btn-success" type="submit" id= "add-button" name="add-button" data-bs-toggle="modal" data-bs-target="#myModal"><span class="las la-plus" style="margin-right: 3px;"></span>New Course</button>
				</div>
			</div>
    </div>		
      	<form method="post" id="filters">
      			<div id="form_div">
      				<div class="filter-div">	
				      	<label>Type: </label>
      					<select name="type" id="type" onchange="this.form.submit()">
      						<option <?php if(isset($_POST['type']) && $_POST['type'] == 'All') echo 'selected';?>>All</option>
                			<option value="1" <?php if(isset($_POST['type']) && $_POST['type'] == 1) echo 'selected'; ?>>Strand</option>
                			<option value="2" <?php if(isset($_POST['type']) && $_POST['type'] == 2) echo 'selected'; ?>>Course</option>
      					</select>
			      	</div>
            	</div>
      	</form>
      	<div id="tables_div">
					<table id="tbl"  class="table table-hover">
						<thead class="table-success">
							<tr>
							<th>Strand/Course</th>
							<th>Acronym</th>
							<th>Type</th>
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

<!-- Modal for New and Edit Courses -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title for_add" id="myModalLabel">Add Course</h5>
        <h5 class="modal-title for_edit" id="myModalLabel">Edit Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="modal-form" method="post">
	      <div class="modal-body">
	      	<input id="edit-id" name="edit-id" type="hidden" required>
	      	<div class="mb-3">
			    <label for="modal-course" class="form-label">Course/Strand Name</label>
			    <input type="text" class="form-control" id="modal-course" name="modal-course" required>
			</div>
			<div class="mb-3">
			    <label for="modal-acronym" class="form-label">Acronym</label>
			    <input type="text" class="form-control" id="modal-acronym" name="modal-acronym" required>
			</div>
			<div class="mb-3">
			    <label for="modal-type" class="form-label">Type</label>
			    <select name="modal-type" id="modal-type" required>
					<option value="2">Course</option>
					<option value="1">Strand</option>
				</select>
			</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
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
       		 	<h5 class="modal-title" id="myModalLabel">Upload Courses</h5>
       			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      		</div>
	      	<div class="modal-body">
	      		<div style="display: flex; justify-content: space-between;">
		      		<div>
		      			<a href="../../templates/CoursesTemplate.xlsx" download id="template"><span class="las la-download" style="margin-right: 3px"></span>CoursesTemplate.xlsx</a>
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
	<script type="text/javascript">
		$(document).ready(function() {
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

			// Show the modal window when the add button is clicked
			$('#add-button').click(function() {
				$('#modal-course').val("");
		  		$('#modal-acronym').val("");
				$('.for_edit').hide();
				$('.for_add').show();
			});

			// Attach the click event listener to the table and handle clicks on the edit button
			$('table').on('click', '.edit-button', function() {
				$('.for_add').hide();
				$('.for_edit').show();
			    var id = $(this).data('id');
			    $('#edit-id').val(id);
			    $.ajax({
						url: 'EditCourses.php',
						method: 'POST',
						data: {id: id},
						dataType: 'json',
						success: function(response) {
						    if (response.status === 'success') {
						        $('#modal-course').val(response.data.course);
						        $('#modal-acronym').val(response.data.acronym);
						        $('#modal-type').val(response.data.type).prop('selected', true);
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
			  var acronym = $(this).data('acronym');
			  const option = confirm('Delete ' + acronym + '?');
			  if (option) {
			    // execute the query
			    $.ajax({
			      url: 'DeleteCourses.php',
			      type: 'post',
			      data: {acronym: acronym},
			      success: function(response) {
			        document.location = "Courses.php";
			      },
			      error: function(xhr, status, error) {
			        alert("Error! Please try again later.");
			        console.log(xhr.responseText);
			      }
			    });
			  }
			});
		});
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
</style>