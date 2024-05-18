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
	<title>ACTS Web Portal | Faculty and Staff</title>
	<!-- bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<!-- styles -->
  	<link rel="stylesheet" href="../../css/navigation.css"/>
  	<link rel="stylesheet" href="../../css/style.css"/>
  	<link rel="stylesheet" href="../../css/DataTables.css"/>
  <!-- icons -->
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
	 <!-- phone number -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
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
include('NavigationBar.php');

 	function showTable(){
 		global $table_contents, $con;
 		$table_contents = "";
 		$sql= "select * from faculty";
 		$result=$con->query($sql);
		while ($row = $result->fetch_assoc()) {

			// Generate edit button and set data-username attribute
    		$edit_button = '<button type="button" class="edit-button btn btn-success" data-id="'.$row["id"].'" data-bs-toggle="modal" data-bs-target="#myModal"><i class="las la-edit"></i></button>';
    		$edit_button = str_replace("\r\n", "", $edit_button); // remove new lines

    		$delete_button = '<button type="button" class="delete-button btn btn-danger" data-id="'.$row["id"].'"><i class="las la-trash"></i></button>';
    		$delete_button = str_replace("\r\n", "", $delete_button); // remove new lines

 				$table_contents .= '<tr>
				<td>' .$row["id"] .'</td>
				<td>' .$row['prefix'].' '.$row["fname"].' '.substr($row["mname"], 0, 1).'. '. $row["lname"].'</td>
				<td>' .$row["email"] .'</td>
				<td>' .$row["phone_number"] .'</td>
				<td>
				'.$edit_button.$delete_button.'</td>
			</tr>';
		} 
 	}

 	showTable();
	?>



<?php 


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

		    $row_fname = $rowData[0][0];
		    $row_mname = $rowData[0][1];
		    $row_lname = $rowData[0][2];
		    $row_prefix = $rowData[0][3];
		    $row_email = $rowData[0][4];
		    $row_number = $rowData[0][5];

		    $sql = "SELECT email FROM (SELECT email FROM user_accounts UNION SELECT email FROM faculty) as combined_email where combined_email.email = '$row_email'";
	    	$result = mysqli_query($con, $sql);

			if (mysqli_num_rows($result) == 0) {
			    // Insert the data into the database
			    $stmt = $con->prepare("INSERT INTO faculty (fname, mname, lname, prefix, email, phone_number) VALUES (?,?,?,?,?,?)");
				$stmt->bind_param("ssssss", $row_fname, $row_mname, $row_lname, $row_prefix, $row_email, $row_number);

				// Execute the statement and check for errors
				if (!$stmt->execute()) {
				    echo "<script>alert('Error: " . $stmt->error . "');</script>";
				}
			} else {
			    echo "<script>alert('Skipping faculty with with email = $row_email');</script>";
			}
		}
		echo "<script type='text/javascript'>document.location ='Faculty.php';</script>";
    } catch (Exception $e) {
        echo 'Error loading file: ',  $e->getMessage(), "\n";
    }
}

if (isset($_POST['add'])) {
			// get input data
            $add_fname = $_POST['modal-fname'];
            $add_mname = $_POST['modal-mname'];
            $add_lname = $_POST['modal-lname'];
			$add_prefix = $_POST['modal-prefix'];
            $add_email = $_POST['modal-email'];
            $add_number = $_POST['modal-number'];
	 		$addImage = null;

				                //check if the new email is already in use
				                $sql_check_email = "SELECT email FROM (SELECT email FROM user_accounts UNION SELECT email FROM faculty) as combined_email where combined_email.email = '$add_email'";
				                $result_check_email = mysqli_query($con, $sql_check_email);
				                if ($result_check_email && mysqli_num_rows($result_check_email) > 0) {
				                    echo "<script>alert('Email already in use!');</script>";
				                } else {
				                	// Check if image file is uploaded
					                if(!empty($_FILES['file-input']['name'])) {
					                    $tmpName = $_FILES['file-input']['tmp_name'];
					                    $addImage = base64_encode(file_get_contents(addslashes($tmpName)));
					                }

				                  // Prepare and bind statement
													$stmt = $con->prepare("INSERT INTO faculty (fname, mname, lname, prefix, email, phone_number, image) VALUES (?,?,?,?,?,?,?)");
													$stmt->bind_param("sssssss", ucwords($add_fname), ucwords($add_mname), ucwords($add_lname), $add_prefix, $add_email, $add_number, $addImage);
													// Check for errors
													if($stmt->errno) {
													  echo "Error: " . $stmt->error;
													}

													// Execute the statement
													if ($stmt->execute()) {
													  echo "<script type='text/javascript'>document.location ='Faculty.php';</script>";
													} else {
													  echo "<script>alert('Error! Try again later.');</script>";
													}

													//close the statement
													mysqli_stmt_close($stmt);
				                }
                    }
        
if (isset($_POST['update'])) {
					$edit_id = $_POST['edit-id'];
					$error_alert = "<script>alert('Error! Try again later.');</script>";

				  $query = "SELECT fname, mname, lname, prefix, email, phone_number FROM faculty WHERE id = ?";
				    $stmt = mysqli_prepare($con, $query);
				    mysqli_stmt_bind_param($stmt, "i", $edit_id);
				    mysqli_stmt_execute($stmt);
				    $ret = mysqli_stmt_get_result($stmt)->fetch_assoc();
				    mysqli_stmt_close($stmt);

				    $old_fname = $ret['fname'];
				    $old_mname = $ret['mname'];
				    $old_lname = $ret['lname'];
				    $old_prefix = $ret['prefix'];
				    $old_email = $ret['email'];
				    $old_number = $ret['phone_number'];
	          $edit_fname = $_POST['modal-fname'];
	          $edit_mname = $_POST['modal-mname'];
	          $edit_lname = $_POST['modal-lname'];
			  $edit_prefix = $_POST['modal-prefix'];
	          $edit_email = $_POST['modal-email'];
	          $edit_number = $_POST['modal-number'];
	          $edit_image = null;

				    // Update Fname
				    if ($old_fname != $edit_fname) {
			        		$sql = "UPDATE faculty SET fname=? WHERE id=?";
			            $stmt = mysqli_prepare($con, $sql);
			            mysqli_stmt_bind_param($stmt, "si", ucwords($edit_fname), $edit_id);
			            if (mysqli_stmt_execute($stmt)) {
			                // Fname updated successfully
			            } else {
			                // Display an error message if the fname update fails
			                echo $error_alert;
			            }
			            mysqli_stmt_close($stmt);
				    }

				    // Update Mname
				    if ($old_mname != $edit_mname) {
			        		$sql = "UPDATE faculty SET mname=? WHERE id=?";
			            $stmt = mysqli_prepare($con, $sql);
			            mysqli_stmt_bind_param($stmt, "si", ucwords($edit_mname), $edit_id);
			            if (mysqli_stmt_execute($stmt)) {
			                // Mname updated successfully
			            } else {
			                // Display an error message if the mname update fails
			                echo $error_alert;
			            }
			            mysqli_stmt_close($stmt);
				    }

				    // Update Lname
				    if ($old_lname != $edit_lname) {
			        		$sql = "UPDATE faculty SET lname=? WHERE id=?";
			            $stmt = mysqli_prepare($con, $sql);
			            mysqli_stmt_bind_param($stmt, "si", ucwords($edit_lname), $edit_id);
			            if (mysqli_stmt_execute($stmt)) {
			                // Lname updated successfully
			            } else {
			                // Display an error message if the lname update fails
			                echo $error_alert;
			            }
			            mysqli_stmt_close($stmt);
				    }

				     // Update Prefix
				    if ($old_prefix != $edit_prefix) {
			        		$sql = "UPDATE faculty SET prefix=? WHERE id=?";
			            $stmt = mysqli_prepare($con, $sql);
			            mysqli_stmt_bind_param($stmt, "si", $edit_prefix, $edit_id);
			            if (mysqli_stmt_execute($stmt)) {
			                // prefix updated successfully
			            } else {
			                // Display an error message if the prefix update fails
			                echo $error_alert;
			            }
			            mysqli_stmt_close($stmt);
				    }

				    // Update Email
				    if ($old_email != $edit_email) {
				    	//check if the new email is unique
              $sql = "SELECT id FROM faculty WHERE email=?";
			        $stmt = mysqli_prepare($con, $sql);
			        mysqli_stmt_bind_param($stmt, "s", $edit_email);
			        mysqli_stmt_execute($stmt);
			        $result = mysqli_stmt_get_result($stmt);
			        if ($result && mysqli_num_rows($result) > 0) {
			            echo "<script>alert('Email already in use.');</script>";
			        } else {
			        		$sql = "UPDATE faculty SET email=? WHERE id=?";
			            $stmt = mysqli_prepare($con, $sql);
			            mysqli_stmt_bind_param($stmt, "si", $edit_email, $edit_id);
			            if (mysqli_stmt_execute($stmt)) {
			                // Email updated successfully
			            } else {
			                // Display an error message if the faculty ID update fails
			                echo $error_alert;
			            }
			            mysqli_stmt_close($stmt);
			        }
				    }

				    // Update Number
				    if ($old_number != $edit_number) {
			        		$sql = "UPDATE faculty SET phone_number=? WHERE id=?";
			            $stmt = mysqli_prepare($con, $sql);
			            mysqli_stmt_bind_param($stmt, "si", $edit_number, $edit_id);
			            if (mysqli_stmt_execute($stmt)) {
			                // Number updated successfully
			            } else {
			                // Display an error message if the faculty ID update fails
			                echo $error_alert;
			            }
			            mysqli_stmt_close($stmt);
				    }

						// Update Image
						if (!empty($_FILES['file-input']['name'])) {
							$tmpName = $_FILES['file-input']['tmp_name'];
							$edit_image = base64_encode(file_get_contents(addslashes($tmpName)));
							// Update the image in the database
						        $sql = "UPDATE faculty SET image=? WHERE id=?";
						        $stmt = mysqli_prepare($con, $sql);
						        mysqli_stmt_bind_param($stmt, "si", $edit_image, $edit_id);
						        if (mysqli_stmt_execute($stmt)) {
						            // Image updated successfully
						        } else {
						            echo $error_alert;
						        }
						        mysqli_stmt_close($stmt);
						}
						//refresh
						echo "<script type='text/javascript'>document.location ='Faculty.php';</script>";
}

 ?>
	<main>
		<div class="title-div flex gap">
      <div>
        	<h5 class="title" id="main-title">Faculty:</h5>  
      	</div>
      	<div style="display: inline-flex; gap: 5px;">
				<div>
				  <button class="btn btn-success" type="submit" id= "upload-button" name="upload-button" data-bs-toggle="modal" data-bs-target="#bulkModal"><span class="las la-upload" style="margin-right: 3px;"></span>Upload Faculties</button>
				</div>
		        <div>
			  <button class="btn btn-success" type="submit" id= "add-button" name="add-button" data-bs-toggle="modal" data-bs-target="#myModal"><span class="las la-plus" style="margin-right: 3px;"></span>New Faculty</button>
			</div>
			</div>
    </div>
      	<div id="tables_div">
		<table id="tbl"  class="table table-hover">
			<thead class="table-success">
				<tr>
				<th>Faculty ID</th>
				<th>Name</th>
				<th>Email</th>
				<th>Phone Number</th>
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
        <h5 class="modal-title for_add" id="myModalLabel">New Faculty</h5>
        <h5 class="modal-title for_edit" id="myModalLabel">Edit Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="modal-form" method="post" enctype="multipart/form-data">
	      <div class="modal-body">
	      	<input id="edit-id" name="edit-id" type="hidden" required>
	      	<div id="user-image" class="mb-3">
	        <?php
	        $img_src = 'data:image;base64,' . $image;
	        echo "<img id='img-preview-file-input' src='$img_src' onerror='this.src=\"../../images/user-icon.png\"' style='width: auto; height: 150px; object-fit: contain'>";
	        ?>
	        <input id="file-input" class="file" type="file" name="file-input" onchange="previewImage(event)">
	      	</div>
			<div class="mb-3">
			    <label for="modal-fname" class="form-label">First Name</label>
			    <input id="modal-fname" name="modal-fname" type="text" class="form-control" required>
			</div>
			<div class="mb-3">
			    <label for="modal-mname" class="form-label">Middle Name</label>
			    <input id="modal-mname" name="modal-mname" type="text" class="form-control" required>
			</div>
			<div class="mb-3">
			    <label for="modal-lname" class="form-label">Last Name</label>
			    <input id="modal-lname" name="modal-lname" type="text" class="form-control" required>
			</div>
			<div class="mb-3">
			    <label for="modal-prefix" class="form-label">Prefix</label>
			    <input id="modal-prefix" name="modal-prefix" type="text" class="form-control" required>
			</div>
			<div class="mb-3">
				<label for="modal-email" class="form-label">Email</label>
				<input id="modal-email" name="modal-email" type="email" class="form-control" required>
			</div>
			<div class="tel-div mb-3">
				<label for="modal-number" class="form-label">Phone Number</label>
				<input id="modal-number" name="modal-number" type="tel" class="form-control" required>
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
       		 	<h5 class="modal-title" id="myModalLabel">Upload Faculties</h5>
       			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      		</div>
	      	<div class="modal-body">
	      		<div style="display: flex; justify-content: space-between;">
		      		<div>
		      			<a href="../../templates/FacultiesTemplate.xlsx" download id="template"><span class="las la-download" style="margin-right: 3px"></span>FacultiesTemplate.xlsx</a>
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
			  		"columnDefs": [
            			{ "orderable": false, "targets": [4] }
        			],
			    	dom: '<"top"fB><l>t<"bottom"i>p<"clear">',
			    	buttons: [
		            'copy', 'csv', 'excel', 'pdf', 'print'
		        ],
			    	initComplete: function() {
			     		$('.my-custom-element').prepend($('#filters'));
			    	}
			  	});

			  	// Show the modal window when the add button is clicked
				$('#add-button').click(function() {
					$('#img-preview-file-input').attr('src', '../../images/user-icon.png');
					$('#file-input').val("");
					$('#modal-fid').val("");
			  		$('#modal-fname').val("");
			  		$('#modal-mname').val("");
			  		$('#modal-lname').val("");
					$('#modal-prefix').val("");
			  		$('#modal-email').val("");
			  		$('#modal-number').val("");
					$('.for_edit').hide();
					$('.for_add').show();
				});

//edit faculty
$('table').on('click', '.edit-button', function() {
	$('.for_add').hide();
	$('.for_edit').show();
  var id = $(this).data('id');
  $('#edit-id').val(id);
  $.ajax({
    url: 'EditFaculty.php',
    method: 'POST',
    data: {id: id},
    dataType: 'json',
    success: function(response) {
      if (response.status === 'success') {
        $('#modal-fname').val(response.data.fname);
        $('#modal-mname').val(response.data.mname);
        $('#modal-lname').val(response.data.lname);
      	$('#modal-prefix').val(response.data.prefix);              
        $('#modal-email').val(response.data.email);
        $('#modal-number').val(response.data.number);
        $('#img-preview-file-input').attr('src', 'data:image;base64,' + response.data.image);
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
					$('.table').on('click', '.delete-button', function() {
					  var id = $(this).data('id');
					  const option = confirm('Delete the faculty with ID = ' + id + ' ?');
					  if (option) {
					    // execute the query
					    $.ajax({
					      url: 'DeleteFaculty.php',
					      type: 'post',
					      data: {id: id},
					      success: function(response) {
					        document.location = "Faculty.php";
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

});
/* Preview the image selected by user in input element. */
					function previewImage(event) {
					  const file = event.target.files[0];
					  const reader = new FileReader();

					  if (!file || !file.type.match(/image\/(png|jpe?g|jfif)/)) {
					    alert("Only PNG, JPG, JPEG, and JFIF files are allowed!");
					    $('#file-input').val("");
					    return;
					  }

					  if (file.size > 2 * 1024 * 1024) {
					    alert("Image size exceeds 2MB limit!");
					    document.getElementById('file-input').value = '';
					    return;
					  }

					  reader.onload = function() {
					    document.getElementById(`img-preview-file-input`).src = reader.result;
					  }
					  reader.readAsDataURL(file);
					}

function initializeTelephoneInputById() {
  const phoneInputField = document.getElementById('modal-number');

  // Remove placeholder text
  phoneInputField.placeholder = "";

  const phoneInput = window.intlTelInput(phoneInputField, {
    initialCountry: "ph",
    preferredCountries: ["ph", "hk", "us"],
    utilsScript:
      "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
  });

  // Set input field value to the initial country's dial code
  const updatePhoneFieldValue = () => {
    const selectedCountryData = phoneInput.getSelectedCountryData();
    const dialCode = selectedCountryData.dialCode;
    phoneInputField.value = dialCode ? "+" + dialCode : "";
  }
  updatePhoneFieldValue();

  // Update input field value when user selects a new country
  phoneInputField.addEventListener("countrychange", updatePhoneFieldValue);
}

initializeTelephoneInputById();

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


.tel-div{
	display: flex;
	flex-direction: column;
	padding: 0;
}

	#user-image {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

/*hide spin button on input type number*/
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}


div.dt-buttons {
	margin-top: 25px;
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