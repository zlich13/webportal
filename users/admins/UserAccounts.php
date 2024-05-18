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

require_once '../../PhpSpreadsheet-1.28.0/src/PhpSpreadsheet/IOFactory.php';
require '../../PhpSpreadsheet-1.28.0/vendor/autoload.php';

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ACTS Web Portal | User Accounts</title>
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

function showTable($type) {
    global $table_contents, $con;
    $table_contents = "";

    $sql = "SELECT * FROM user_accounts";

    if ($type !== 'All') {
	 	$sql = $sql." WHERE account_type = '$type'";
	}

    $result = $con->query($sql);
    while ($row = $result->fetch_assoc()) {
        $user_type = $row["account_type"];
        $u_type = "";
        switch ($user_type) {
            case 3:
                $u_type = "Superadmin";
                break;
            case 2:
                $u_type = "Admin";
                break;
            case 1:
                $u_type = "Student";
                break;
        }

        // Generate edit button and set data-username attribute
        $edit_button = '<button type="button" class="edit-button btn btn-success" data-id="'.$row["id"].'" data-bs-toggle="modal" data-bs-target="#myModal"><i class="las la-edit"></i></button>';
        $edit_button = str_replace(["\r", "\n"], "", $edit_button); // remove new lines

        $delete_button = '<button type="button" class="delete-button btn btn-danger" data-username="'.$row["username"].'"><i class="las la-trash"></i></button>';
        $delete_button = str_replace(["\r", "\n"], "", $delete_button); // remove new lines

        $table_contents .= '<tr>
            					<td>'.$row["username"].'</td>
            					<td>'.$row["email"].'</td>
            					<td>'.$u_type.'</td>
            					<td>'.$edit_button.$delete_button.'</td>
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
		    $row_username = $rowData[0][0];
		    $row_email = $rowData[0][1];
		    $row_password = $rowData[0][2];
		    $row_type = $rowData[0][3];
		    
		    $sql = "SELECT id FROM user_accounts WHERE username ='$row_username' OR email='$row_email'";
	    	$result = mysqli_query($con, $sql);

			if (mysqli_num_rows($result) == 0) {
				$hash_pass = password_hash($row_password, PASSWORD_DEFAULT);
			    // Insert the data into the database
			    $stmt = $con->prepare("INSERT INTO user_accounts (username, email, password, account_type) VALUES (?, ?, ?, ?)");
				$stmt->bind_param("sssi", $row_username, $row_email, $hash_pass, $row_type);

				// Execute the statement and check for errors
				if (!$stmt->execute()) {
				    echo "<script>alert('Error: " . $stmt->error . "');</script>";
				}
			} else {
			    echo "<script>alert('Skipping account with username = $row_username');</script>";
			}
		}
		echo "<script type='text/javascript'>document.location ='UserAccounts.php';</script>";
    } catch (Exception $e) {
        echo 'Error loading file: ',  $e->getMessage(), "\n";
    }
}

if (isset($_POST['add'])) {
	// get input data
		$add_username = $_POST['modal-username'];
	    $add_email = $_POST['modal-email'];
	    $add_pass = trim($_POST['modal-password']);
	    $add_type = $_POST['modal-type'];
	    $add_image = null;

	    // Check if the new username is already taken
	    $sql = "SELECT id FROM user_accounts WHERE username = '$add_username' ";
	    $result = mysqli_query($con, $sql);
	    if ($result && mysqli_num_rows($result) > 0) {
	        echo "<script>alert('Username already taken.');</script>";
	    } else {
	    	// Check if the new email is already in use
	        $sql_check_email = "SELECT email FROM (SELECT email FROM user_accounts UNION SELECT email FROM faculty) as combined_email where combined_email.email = '$add_email'";
	        $result_check_email = mysqli_query($con, $sql_check_email);
	        if ($result_check_email && mysqli_num_rows($result_check_email) > 0) {
	            echo "<script>alert('Email already in use.');</script>";
	        } else {
	        	 // Check if the password is at least 8 characters
	            if (strlen($add_pass) < 8) {
	                echo "<script>alert('Password must be at least 8 characters');</script>";
	            } else {
	            	// Hash the password
                	$hash_pass = password_hash($add_pass, PASSWORD_DEFAULT);

                	// Check if image file is uploaded
	                if(!empty($_FILES['file-input']['name'])) {
	                    $tmpName = $_FILES['file-input']['tmp_name'];
	                    $add_image = base64_encode(file_get_contents(addslashes($tmpName)));
	                }

	                // Prepare and bind statement
					$stmt = $con->prepare("INSERT INTO user_accounts (username, email, password, account_type, image) VALUES (?,?,?,?,?)");
					$stmt->bind_param("sssis", $add_username, $add_email, $hash_pass, $add_type, $add_image);

					// Execute the statement
					if ($stmt->execute()) {
						echo "<script type='text/javascript'>document.location ='UserAccounts.php';</script>";
					} else {
						echo "<script>alert('Error! Try again later.');</script>";
					}
					// close the statement
					mysqli_stmt_close($stmt);
	            }
	        }
    	}
}

if (isset($_POST['update'])) {
	$edit_id = $_POST['edit-id'];
	$error_alert = "<script>alert('Error! Try again later.');</script>";

	$query = "SELECT username, email, account_type FROM user_accounts WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $ret = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    $old_username = $ret['username'];
    $old_email = $ret['email'];
    $old_type = $ret['type'];

	$edit_username = $_POST['modal-username'];
	$edit_email = $_POST['modal-email'];
	$edit_pass = trim($_POST['modal-password']);
	$edit_type = $_POST['modal-type'];
	$edit_image = null;

	// Update username
    if ($old_username != $edit_username) {
        $sql = "SELECT id FROM user_accounts WHERE username=?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $edit_username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<script>alert('Username already taken.');</script>";
        } else {
            $sql = "UPDATE user_accounts SET username=? WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", $edit_username, $edit_id);
            if (mysqli_stmt_execute($stmt)) {
                // Username updated successfully
            } else {
                // Display an error message if the username update fails
                echo $error_alert;
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Update email
    if ($old_email != $edit_email) {
        $sql_check_email = "SELECT email FROM (SELECT email FROM user_accounts UNION SELECT email FROM faculty) as combined_email WHERE combined_email.email = ?";
        $stmt = mysqli_prepare($con, $sql_check_email);
        mysqli_stmt_bind_param($stmt, "s", $edit_email);
        mysqli_stmt_execute($stmt);
        $result_check_email = mysqli_stmt_get_result($stmt);
        if ($result_check_email && mysqli_num_rows($result_check_email) > 0) {
            echo "<script>alert('Email already in use.');</script>";
        } else {
            $sql = "UPDATE user_accounts SET email=? WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", $edit_email, $edit_id);
            if (mysqli_stmt_execute($stmt)) {
                // Email updated successfully
            } else {
                // Display an error message if the email update fails
                echo $error_alert;
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Update type
    if ($old_type != $edit_type) {
        $sql = "UPDATE user_accounts SET account_type=? WHERE id=?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "si", $edit_type, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            // Type updated successfully
        } else {
            // Display an error message if the password update fails
            echo $error_alert;
        }
        mysqli_stmt_close($stmt);
    }

    //Password Validation
    if (!empty($edit_pass)) {
	    if (strlen($edit_pass) < 8) {
	        // Check if the password is at least 8 characters
	        echo "<script>alert('Password must be at least 8 characters');</script>";
	    } else {
	        // Hash the password
	        $hash_pass = password_hash($edit_pass, PASSWORD_DEFAULT);
	        // Update the password in the database
	        $sql = "UPDATE user_accounts SET password=? WHERE id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "si", $hash_pass, $edit_id);
	        if (mysqli_stmt_execute($stmt)) {
	            // Password updated successfully
	        } else {
	            // Display an error message if the password update fails
	            echo $error_alert;
	        }
	        mysqli_stmt_close($stmt);
	    }
	}

	// Image validation
	if (!empty($_FILES['input-file']['name'])) {
		$tmpName = $_FILES['input-file']['tmp_name'];
		$edit_image = base64_encode(file_get_contents(addslashes($tmpName)));
		// Update the image in the database
	        $sql = "UPDATE user_accounts SET image=? WHERE id=?";
	        $stmt = mysqli_prepare($con, $sql);
	        mysqli_stmt_bind_param($stmt, "si", $edit_image, $edit_id);
	        if (mysqli_stmt_execute($stmt)) {
	            // Image updated successfully
	        } else {
	            // Display an error message if the password update fails
	            echo $error_alert;
	        }
	        mysqli_stmt_close($stmt);
	}
	//refresh
	echo "<script type='text/javascript'>document.location ='UserAccounts.php';</script>";
}
?>
<main>
	<div class="title-div">
		<div>
	 		 <h5 class="title" id="main-title">User Accounts:</h5>
		</div>
		<div style="display: flex; gap: 5px;">
		        <div>
				  <button class="btn btn-success" type="submit" id= "upload-button" name="upload-button" data-bs-toggle="modal" data-bs-target="#bulkModal"><span class="las la-upload" style="margin-right: 3px;"></span>Upload Users</button>
				</div>
				<div>
			    	<button class="btn btn-success" type="submit" id="add-button" name="add-button" data-bs-toggle="modal" data-bs-target="#myModal"><span class="las la-plus" style="margin-right: 3px;"></span>New User</button>
			  	</div>
		</div>
	</div>
	<form method="post" id="filters">
	  <div id="form_div">
	  	<div class="filter-div">
	    <label for="type">Type:</label>
	    <select name="type" id="type" onchange="this.form.submit()">
	      <option <?php if(!isset($_POST['type']) || $_POST['type'] == 'All') echo 'selected'; ?>>All</option>
	      <option value="3" <?php if(isset($_POST['type']) && $_POST['type'] == 3) echo 'selected'; ?>>Superadmin</option>
	      <option value="2" <?php if(isset($_POST['type']) && $_POST['type'] == 2) echo 'selected'; ?>>Admin</option>
	      <option value="1" <?php if(isset($_POST['type']) && $_POST['type'] == 1) echo 'selected'; ?>>Student</option>
	    </select>
	    </div>
	  </div>
	</form>

	<div id="tables_div">
	  <table id="tbl" class="table table-hover">
	    <thead class="table-success">
	      <tr>
	        <th>Username</th>
	        <th>Email</th>
	        <th>Account Type</th>
	        <th></th>
	      </tr>
	    </thead>
	    <tbody>
	      <?php echo $table_contents; ?>
	    </tbody>
	  </table>
	</div>


<!-- Modal for New and Edit Accounts -->
<div class="modal" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title for_add" id="myModalLabel">New Account</h5>
        <h5 class="modal-title for_edit" id="myModalLabel">Edit Account</h5>
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
	        <input id="input-file" class="file" type="file" name="input-file" onchange="previewImage(event)">
	      	</div>
	      	<div class="mb-3">
	          <label for="modal-username" class="form-label">Username</label>
	          <input id="modal-username" name="modal-username" type="text" class="form-control"required>
	        </div>
	        <div class="mb-3">
	          <label for="modal-email" class="form-label">Email</label>
	          <input id="modal-email" name="modal-email" type="email" class="form-control"required>
	        </div>
	        <div class="mb-3">
	          <label for="modal-password" class="form-label">Password</label>
	          <input id="modal-password" name="modal-password" type="text" placeholder="Leave this blank if you don't want to change password." class="form-control">
	        </div>
	        <div class="mb-3">
	          <label for="modal-type" class="form-label">Type</label>
	          <select name="modal-type" id="modal-type" required>
	            <option value="3">Superadmin</option>
	            <option value="2">Admin</option>
	            <option value="1">Student</option>
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
       		 	<h5 class="modal-title" id="myModalLabel">Upload Accounts</h5>
       			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      		</div>
	      	<div class="modal-body">
	      		<div style="display: flex; justify-content: space-between;">
		      		<div>
		      			<a href="../../templates/UserAccountsTemplate.xlsx" download id="template"><span class="las la-download" style="margin-right: 3px"></span>UserAccountsTemplate.xlsx</a>
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
			"targets": [0, 3]
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
				$('#img-preview-file-input').attr('src', '../../images/user-icon.png');
				$('#file-input').val("");
				$('#modal-username').val("");
		  		$('#modal-email').val("");
		  		$('#modal-password').val("");
				$('.for_edit').hide();
				$('.for_add').show();
			});

	// Handle clicks on the edit button in the table
	$('table').on('click', '.edit-button', function() {
		$('.for_add').hide();
		$('.for_edit').show();
		var id = $(this).data('id');
		$('#edit-id').val(id);
			$.ajax({
				url: 'EditUserAccounts.php',
				method: 'POST',
				data: {id: id},
				dataType: 'json',
				success: function(response) {
				    if (response.status === 'success') {
				    	$('#file-input').val("");
				        $('#modal-username').val(response.data.username);
				        $('#modal-email').val(response.data.email);
				        $('#modal-password').val("");
				        $('#modal-type').val(response.data.type).prop('selected', true);
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

	// Handle clicks on the delete button in the table
	$('.table').on('click', '.delete-button', function() {
		var username = $(this).data('username');
		const option = confirm('Delete the account with username "' + username + '" ?');
		if (option) {
			// execute the query
			$.ajax({
				url: 'DeleteUserAccounts.php',
				type: 'post',
				data: {username: username},
				success: function(response) {
					document.location = "UserAccounts.php";
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
<!-- Preview the image selected by user in input element. -->
<script src="../../js/preview_image.js"></script>
</body>
</html>
<style type="text/css">
	.filter-div{
		width: 100%;
	}

	#user-image {
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  flex-direction: column;
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