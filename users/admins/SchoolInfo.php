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

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ACTS Web Portal | School Information</title>
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

if (isset($_POST['update'])) {
	$sc_name=$_POST['sc_name'];
	$sc_add=$_POST['sc_add'];
	$sc_num=$_POST['sc_num'];
	$sql = "UPDATE school_info SET sc_name=?, sc_add=?, sc_num=? WHERE id=1";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sss", strtoupper($sc_name), $sc_add, $sc_num);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script type='text/javascript'>document.location ='SchoolInfo.php';</script>";
            } else {
               echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
            }
            mysqli_stmt_close($stmt);
}

$semester = isset($sy_ret['semester']) ? $sy_ret['semester'] : '';

if (isset($_POST['sem_btn'])) {
	mysqli_begin_transaction($con);	

    $sql = "UPDATE semesters SET sem_is_active = CASE WHEN sy_id = ? AND semester = 2 THEN 1 ELSE 0 END, sem_end = CASE WHEN sy_id = ? AND semester = 1 THEN current_timestamp() ELSE sem_end END, sem_start = CASE WHEN sy_id = ? AND semester = 2 THEN current_timestamp() ELSE sem_start END";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $sy_ret['sy_id'], $sy_ret['sy_id'], $sy_ret['sy_id']);
    if (mysqli_stmt_execute($stmt)) {
    	$sql = "UPDATE student_list SET enrolled_status = 0 WHERE enrolled_status = ?";
		$stmt = mysqli_prepare($con, $sql);
		$enrolled_status = 1;
		mysqli_stmt_bind_param($stmt, "i", $enrolled_status);
		if (mysqli_stmt_execute($stmt)) {
			mysqli_commit($con);
		    echo "<script type='text/javascript'>document.location ='SchoolInfo.php';</script>";
		} else {
		    mysqli_rollback($con);
		    echo "<script>alert('Error updating students' status: " . mysqli_error($con) . "');</script>";    
		}
    } else {
    	mysqli_rollback($con);
        echo "<script>alert('Error updating semester: " . mysqli_error($con) . "');</script>";
    }
    mysqli_stmt_close($stmt);
}




if (isset($_POST['year_btn'])) {
	mysqli_begin_transaction($con);	

	$new_start=$sy_ret['year_start']+1;
	$new_end=$sy_ret['year_end']+1;

	$sql = "INSERT INTO school_years (year_start, year_end) VALUES (?,?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_start, $new_end);
    if (mysqli_stmt_execute($stmt)) {
    	$scy_id=mysqli_insert_id($con);
    	$sql = "UPDATE school_years SET is_active = CASE WHEN sy_id = ? THEN 1 ELSE 0 END";
		    $stmt = mysqli_prepare($con, $sql);
		    mysqli_stmt_bind_param($stmt, "i", $scy_id);
		if (mysqli_stmt_execute($stmt)) {
	        $sql = "INSERT INTO semesters (semester, sy_id) VALUES (1,?), (2,?)";
			$stmt = mysqli_prepare($con, $sql);
			mysqli_stmt_bind_param($stmt, "ii", $scy_id, $scy_id);
		    if (mysqli_stmt_execute($stmt)) {
		    	$sql = "UPDATE semesters SET sem_is_active = CASE WHEN sy_id = ? AND semester = 1 THEN 1 ELSE 0 END, sem_end = CASE WHEN sem_id = ? THEN current_timestamp() ELSE sem_end END, sem_start = CASE WHEN sy_id = ? AND semester = 1 THEN current_timestamp() ELSE sem_start END";
			    $stmt = mysqli_prepare($con, $sql);
			    mysqli_stmt_bind_param($stmt, "iii", $scy_id, $sy_ret['sem_id'], $scy_id);
			    if (mysqli_stmt_execute($stmt)) {
			    	$sql = "UPDATE student_list SET enrolled_status = ?";
					$stmt = mysqli_prepare($con, $sql);
					mysqli_stmt_bind_param($stmt, "i", $enrolled_status);
					$enrolled_status = 0;
					if (mysqli_stmt_execute($stmt)) {
						mysqli_commit($con);
	      				echo "<script type='text/javascript'>document.location ='SchoolInfo.php';</script>";
					} else {
					    mysqli_rollback($con);
					    echo "<script>alert('Error updating students' status: " . mysqli_error($con) . "');</script>";    
					}
			    } else {
			    	mysqli_rollback($con);
			       echo "<script>alert('Error updating semester: " . mysqli_error($con) . "');</script>";
			    }
		    } else {
		    	mysqli_rollback($con);
		        echo "<script>alert('Error inserting semester: " . mysqli_error($con) . "');</script>";
		    }
	    } else {
	    	mysqli_rollback($con);
	        echo "<script>alert('Error updating school_year: " . mysqli_error($con) . "');</script>";
	    }
	} else {
		mysqli_rollback($con);
		echo "<script>alert('Error inserting school_year: " . mysqli_error($con) . "');</script>";
	}
	mysqli_stmt_close($stmt);
}
?>
<main>
	<div>
	 	<h5 class="title" id="main-title">School Information:</h5>
	</div>
	<div style="display: flex; justify-content: space-between; gap: 20px;">
		<form method="post" style="width: 100%; padding: 20px; border: 1px green solid; border-radius: 5px;">
			<div class="mb-3" >
			    <label for="sc_name" class="form-label">School Name:</label>
			    <input id="sc_name" name="sc_name" type="text" value="<?php echo $info_ret['sc_name']; ?>" class="form-control"required>
			</div>
			<div class="mb-3">
			    <label for="sc_add" class="form-label">Address:</label>
			    <input id="sc_add" name="sc_add" value="<?php echo $info_ret['sc_add']; ?>" type="text" class="form-control"required>
			</div>
			<div class="mb-3">
			    <label for="sc_num" class="form-label">Contact Number:</label>
			    <input id="sc_num" name="sc_num" value="<?php echo $info_ret['sc_num']; ?>"type="text" class="form-control"required>
			</div>
			<div style="text-align: right;"><button id="update" name="update" type="submit" class="btn btn-success">Update</button></div>
		</form>
		<form method="post" style="width: 100%; padding: 20px; border: 1px green solid; border-radius: 5px;">
			<div class="mb-3">
			    <label for="sy_active" class="form-label">School Year:</label>
			    <input id="sy_active" name="sy_active" value="<?php echo $sy_ret['year_start']."-".$sy_ret['year_end'] ?>"type="text" class="form-control" disabled>
			</div>
			<div class="mb-3">	
			   	<label for="sem_active" class="form-label">Semester:</label>
			   	<input id="sem_active" name="sem_active" value="<?php echo $semester == 1 ? 'First' : ($semester == 2 ? 'Second' : '') ?>"type="text" class="form-control" disabled>
			</div>
			<div style="text-align: right;"><?php echo $semester == 1 ? '<button id="end_sem" name="end_sem" type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#sem_modal">End Semester</button>' : ($semester == 2 ? '<button id="end_year" name="end_year" type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#year_modal">End School Year</button>' : '') ?>
			</div>
		</form>
	</div>
	
	<div class="modal fade" id="sem_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="myModalLabel">Attention!</h5>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form id="modal-form" method="post">
		      <div class="modal-body">
		      	<div class="mb-3" style="text-align: center;">
		          <label for="modal_year_start" class="form-label">Are you sure to start the <span style="color: darkred;">second semester</span> for S.Y. <?php echo $sy_ret['year_start']."-".$sy_ret['year_end'] ?>?</label>
		          <small><i>Note: The changes cannot be undone and will make all student's status <span style="color: darkred;">'Not Enrolled'</span></i></small>
		        </div>
		      </div>
		      <div class="modal-footer">
		        <button id="sem_btn" name="sem_btn" type="submit" class="btn btn-success">Confirm</button>
		      </div>
	  	   </form>
	    </div>
	  </div>
	</div>
	<div class="modal fade" id="year_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="myModalLabel" style="color: darkred; font-weight: bold;">Attention!</h5>
	        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <form id="modal-form" method="post">
		      <div class="modal-body">
		      	<div class="mb-3" style="text-align: center;">
		          <label for="modal_year_start" class="form-label">Are you sure to start S.Y. <span style="color: darkred;"><?php echo $sy_ret['year_start'] + 1 ."-". $sy_ret['year_end'] + 1 ?>?</span></label>
		          <p><small><i>Note: The changes cannot be undone and will make all student's status <span style="color: darkred;">'Not Enrolled'</span></i></small></p>
		        </div>
		      </div>
		      <div class="modal-footer">
		        <button id="year_btn" name="year_btn" type="submit" class="btn btn-success">Confirm</button>
		      </div>
	  	   </form>
	    </div>
	  </div>
	</div>
</main>
</body>
</html>