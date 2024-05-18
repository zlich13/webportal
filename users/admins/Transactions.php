<?php 
	session_start();
	error_reporting(0);

	//navigation styles
	$tstyle = "active";

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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ACTS Web Portal | Payment Transactions</title>
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
	//include navigationbar
	include('NavigationBar.php');

	function showTable($mode, $status) {
		//show data on datatable based on mode and status filter
	  global $table_contents, $con;
	  $table_contents = "";
	  $where_conditions = array();

	  if ($mode !== 'All') {
	  	// if mode not all, add where condition
	    $where_conditions[] = "t.mode = '$mode'";
	  }

	  if ($status !== 'All') {
	  	// if status not all, add where condition
	    $where_conditions[] = "t.trans_status = '$status'";
	  }

    if (!empty($where_conditions)) {
    	// add where conditions
      $sql = "SELECT t.*, a.fname, a.mname, a.lname FROM transactions t LEFT OUTER JOIN applications a ON t.user_id = a.user_id WHERE " . implode(' AND ', $where_conditions);
    } else {
      $sql = "SELECT t.*, a.fname, a.mname, a.lname FROM transactions t INNER JOIN applications a ON t.user_id = a.user_id";
    }

    // execute SQL query and generate table contents
		$result = $con->query($sql);
		while ($row = $result->fetch_assoc()) {
	    $remarks = $row['trans_remarks'];
	    $user_id = $row['user_id'];
	    $trans_id = $row['trans_id'];
	    $url = "get_image_db2.php?id=$user_id";

	    // generate view button for proof
	    $view_button = '<a class="view_button btn btn-primary" href="javascript:void(0)" onclick="window.open(\'' . $url . '\', \'_blank\')"><i class="las la-search"></i></a>';
	    $view_button = str_replace("\r\n", "", $view_button); // remove new lines

	    // generate view button for remarks
	    $view_remarks = '<button  type="button" class="view_remarks btn btn-danger" data-bs-toggle="modal" data-bs-target="#remarks_modal" data-remarks="'.$remarks.'" data-url="'.$url.'"><i class="las la-search"></i></button>';
	    $view_remarks = str_replace("\r\n", "", $view_remarks); // remove new lines

	    // generate approve button
	    $approve_button = '<button  type="button" class="approve_button btn btn-success" data-bs-toggle="modal" data-bs-target="#approve_modal" data-uid="'.$user_id.'" data-tid="'.$trans_id.'"><i class="las la-check"></i></button>';
	    $approve_button = str_replace("\r\n", "", $approve_button); // remove new lines

	    // generate reject button for proof
	    $reject_button = '<button type="button" class="reject_button btn btn-danger" data-bs-toggle="modal" data-bs-target="#reject_modal" data-tid="'.$trans_id.'"><i class="las la-times"></i></button>';
	    $reject_button = str_replace("\r\n", "", $reject_button); // remove new lines

	    //convert status to text
			$stat = ($row['trans_status'] == 1) ? "Pending" : 
											(($row['trans_status'] == 2) ? "Approved" : 
					            (($row['trans_status'] == 3) ? "Rejected" : null));

	    $table_contents .= "<tr>
	      <td>" . $row['lname'] . ", " . $row['fname'] . " " .strtoupper(substr($row['mname'],0,1)). "</td>
	      <td>" . $row["ref_num"] . "</td>
	      <td>" . $row["mode"] . "</td>
	      <td>" . $row["sender"] . "</td>
	      <td>" . $row["trans_date"] . "</td>
	      <td>" . $row["amount"] . "</td>
	      <td>" . $row["purpose"] . "</td>
	      <td>" . $stat . "</td>
	      <td>" . $row["trans_process_date"] . "</td>
	      <td>" . $row["trans_process_by"] . "</td>";

	    if ($row['trans_status'] == 1) {
	    	// if pending show view proof, approve and reject button
		    $table_contents =  $table_contents."
		    <td>" . $view_button . $approve_button . $reject_button . "</td>
		    </tr>";
		  } else if ($row['trans_status'] == 2) {
		  	// if approve show view proof button
		  	$table_contents =  $table_contents."
	    	<td>" . $view_button . "</td>
	      </tr>";
		  } else if ($row['trans_status'] == 3){
		  	// if rejected show view remarks button
		  	$table_contents =  $table_contents."
	    	<td>" . $view_remarks . "</td>
	      </tr>";
		  } else {
		  	//show nothing
		  	$table_contents =  $table_contents."
	      </tr>";
		  }
		}
	}

	//show datatable data initially
	$sta = $_GET['sta'];
	showTable('All',$sta);

	// if user selects mode/status, show filtered 
	if(isset($_POST['mode'])||isset($_POST['status'])){
		$mode = $_POST['mode'];
		$status = $_POST['status'];
		showTable($mode,$status);
	}
	
	function sendEmail($subject,$message,$user_email){
	    //php mailer
	    include('../../confirmation_email.php');
	    $recipient = $user_email;
	    send_mail($recipient,$subject,$message);
	}

	if (isset($_POST['approve'])) {
		$user_id = $_POST['u_id'];
		$trans_id = $_POST['t_id'];

		$sy_id = $sy_ret['sy_id'];
	  $sem_id = $sy_ret['sem_id'];
	  $semester = $sy_ret['semester'];

		// check  if student is already admitted
		$query = mysqli_query($con, "SELECT * FROM student_list WHERE user_id = $user_id");
		if(mysqli_num_rows($query) > 0) {
			//select student's info
		$query = mysqli_query($con, "SELECT a.category, s.*, ss.section_id, u.email FROM applications a LEFT OUTER JOIN student_list s ON a.user_id=s.user_id LEFT OUTER JOIN student_section ss ON s.student_num=ss.student_num LEFT OUTER JOIN user_accounts u ON a.user_id=u.id WHERE s.user_id = $user_id");
	  	$ret = mysqli_fetch_array($query);
	  	$student_num = $ret['student_num'];
	  	$category = $ret['category'];
	  	$student_course = $ret['student_course'];
	  	$user_email = $ret['email'];
	  	if ($semester == 2) {
	  		$year = $ret['student_year'];
	  	} else {
	  		$year = $ret['student_year'] + 1;
	  	}

		  mysqli_begin_transaction($con);	

		  //update transaction to approve
	  	$sql = "UPDATE transactions SET trans_status = 2, trans_process_date = current_timestamp(), trans_process_by = ? WHERE trans_id = ?";
	  	$stmt = mysqli_prepare($con, $sql);
	  	mysqli_stmt_bind_param($stmt, "si", $username, $trans_id);

	  	if (mysqli_stmt_execute($stmt)) {
	  		$sql = "UPDATE student_list SET student_year=?, sy_enrolled=?, sem_enrolled=?, enrolled_status=1, enrolled_date=current_timestamp() WHERE user_id = ?";
	  			$stmt = mysqli_prepare($con, $sql);
	  			mysqli_stmt_bind_param($stmt, "iiii", $year, $sy_id, $sem_id, $user_id);
	  			if (mysqli_stmt_execute($stmt)) {
	  				if ($semester == 1) {
	  					$selected_section_id = auto_section($student_num, $year, $student_course, $sy_id);
	  				} else {
	  					$selected_section_id = $ret['section_id'];
	  				}

	  				//if new student enroll the subjects
	      		if ($category == 1) {
		        	$subject_query = mysqli_query($con, "SELECT subject_id FROM subjects WHERE sub_grade_year = $year AND sub_course = $student_course AND semester = $semester");
		        	if (mysqli_num_rows($subject_query) > 0) {
		          	while ($subject = mysqli_fetch_array($subject_query)) {
		            	$subject_id = $subject['subject_id'];
		            	$sql = "INSERT INTO student_subjects (student_num, subject_id, sem_id, school_year_id, section_id) VALUES (?,?,?,?,?)";
		            	$stmt = mysqli_prepare($con, $sql);
		            	mysqli_stmt_bind_param($stmt, "iiiii", $student_num, $subject_id, $sem_id, $sy_id, $selected_section_id);
		            	mysqli_stmt_execute($stmt);
		            	if (mysqli_stmt_error($stmt)) {
		              	mysqli_rollback($con);
		              	echo "<script>alert('Error inserting student subject: " . mysqli_error($con) . "');</script>";
		              	break;
		            	}
		          	}
		        	}
		      	}
						mysqli_commit($con);
						//function to send email
						$subject = "Congratulations on your Admission to ACTS Computer College!";
						$message = "Congratulations! We are thrilled to inform you that you have been admitted to ACTS Computer College. We look forward to welcoming you to our campus community.";
						sendEmail($subject,$message,$user_email);
		      	echo "<script type='text/javascript'>document.location ='Transactions.php?sta=1';</script>";
	  			} else {
	  				mysqli_rollback($con);
	      		echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
	  			}
			} else {
	  		mysqli_rollback($con);
	      echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
	  	}
		} else {
		  //get students info
	 $query = mysqli_query($con, "SELECT a.course, a.year, a.category, u.email FROM applications a JOIN user_accounts u ON a.user_id = u.id WHERE user_id = $user_id");
	  	$ret = mysqli_fetch_array($query);
	  	$year = $ret['year'];
	  	$course = $ret['course'];
	  	$category = $ret['category'];
	  	$user_email = $ret['email'];
	  	mysqli_begin_transaction($con);	
		
			//update transaction to approve
	  	$sql = "UPDATE transactions SET trans_status = 2, trans_process_date = current_timestamp(), trans_process_by = ? WHERE user_id = ?";
	  	$stmt = mysqli_prepare($con, $sql);
	  	mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
	  
	  	//enroll the student
	  	if (mysqli_stmt_execute($stmt)) {
	    	$sql = "INSERT INTO student_list (user_id, student_year, student_course, sy_enrolled, sem_enrolled) VALUES (?,?,?,?,?)";
	    	$stmt = mysqli_prepare($con, $sql);
	    	mysqli_stmt_bind_param($stmt, "iiiii", $user_id, $year, $course, $sy_id, $sem_id);

	    	// enroll the student in a section
	    	if (mysqli_stmt_execute($stmt)) {
	      	$student_num = mysqli_insert_id($con);
	      	$selected_section_id = auto_section($student_num, $year, $course, $sy_id);

	      	//if new student enroll the subjects
	      	if ($category == 1) {
	        $subject_query = mysqli_query($con, "SELECT subject_id FROM subjects WHERE sub_grade_year = $year AND sub_course = '$course' AND semester = '$semester'");
	        	if (mysqli_num_rows($subject_query) > 0) {
	          	while ($subject = mysqli_fetch_array($subject_query)) {
	            	$subject_id = $subject['subject_id'];
	            	$section_id = $selected_section_id;
	            	$sql = "INSERT INTO student_subjects (student_num, subject_id, sem_id, school_year_id, section_id) VALUES (?,?,?,?,?)";
	            	$stmt = mysqli_prepare($con, $sql);
	            	mysqli_stmt_bind_param($stmt, "iiiii", $student_num, $subject_id, $sem_id, $sy_id, $section_id);
	            	mysqli_stmt_execute($stmt);
	            	if (mysqli_stmt_error($stmt)) {
	              	mysqli_rollback($con);
	              	echo "<script>alert('Error inserting student subject: " . mysqli_error($con) . "');</script>";
	              	break;
	            	}
	          	}
	        	}
	      	}
		      mysqli_commit($con);
		      //function to send email
						$subject = "Congratulations on your Admission to ACTS Computer College!";
						$message = "Congratulations! We are thrilled to inform you that you have been admitted to ACTS Computer College. We look forward to welcoming you to our campus community.";
						sendEmail($subject,$message,$user_email);
		      echo "<script type='text/javascript'>document.location ='Transactions.php?sta=1';</script>";
	    	} else {
	      	mysqli_rollback($con);
	      	echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
	    	}
	  	} else {
	    	mysqli_rollback($con);
	    	echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
	  	}
		}
	}

    function auto_section($student_num, $year, $course, $sy_id) {
        // Get the section with the least number of students
        $section = get_available_section($year, $course, $sy_id);

        // If there is no available section, create a new one
        if (!$section) {
          $section = create_new_section($year, $course, $sy_id);
        }
        
        // Enroll the student in the section
        enroll_student_in_section($student_num, $section['section_id']);

         return $section['section_id'];
    }

    function get_available_section($year, $course, $sy_id) {
        // Get all sections for the specified year, course and active school year
        $sections = get_sections_by_year_course_school_year($year, $course, $sy_id);
        
        // Find the section with the least number of students
        $min_students = PHP_INT_MAX;
        $min_section = null;
        foreach ($sections as $section) {
            if ($section['students_count'] < $section['capacity']) {
                if ($section['students_count'] < $min_students) {
                    $min_students = $section['students_count'];
                    $min_section = $section;
                }
            }
        }
        return $min_section;
    }

    function get_sections_by_year_course_school_year($year, $course, $sy_id) {
      global $con;

      $sql = "SELECT * FROM sections WHERE year = ? AND course = ? AND section_sy = ?";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "iii", $year, $course, $sy_id);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      $sections = array();
      while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row;
      }
      return $sections;
    }

    function create_new_section($year, $course, $sy_id) {
        // Determine the next section name (e.g. "A", "B", "C", etc.)
        $sections = get_sections_by_year_course_school_year($year, $course, $sy_id);
        $next_name = chr(65 + count($sections));
        
        // Create the new section in the database
        $section_id = insert_new_section($next_name, $year, $course, $sy_id);
        
        // Return the new section
        return [
            'section_id' => $section_id,
            'name' => $next_name,
            'capacity' => 35,
            'year' => $year,
            'course' => $course,
            'students_count' => 0,
            'section_sy' => $sy_id
        ];
    }

    function insert_new_section($name, $year, $course, $sy_id) {
      global $con;
      $stmt = $con->prepare("INSERT INTO sections (name, year, course, section_sy) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("siii", $name, $year, $course, $sy_id);
      if ($stmt->execute()) {
          return $con->insert_id;
      } else {
          return false;
      }
    }

    function enroll_student_in_section($student_num, $section_id) {
      global $con;
      $sql = "INSERT INTO student_section (student_num, section_id) VALUES (?,?)";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "ii", $student_num, $section_id);
      mysqli_stmt_execute($stmt);
      
      // Update the students_count column in the section_list table
      update_section_students_count($section_id);
    }

    function update_section_students_count($section_id) {
      global $con;
      $sql = "SELECT COUNT(*) as count FROM student_section WHERE section_id = ?";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "i", $section_id);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      
      if ($row = mysqli_fetch_assoc($result)) {
        $count = $row['count'];
        $sql = "UPDATE sections SET students_count = ? WHERE section_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $count, $section_id);
        mysqli_stmt_execute($stmt);
      }
    }

	//reject the application
	if (isset($_POST['reject'])) {
		$trans_id = $_POST['r_tid'];
	  $remarks = $_POST['remarks'];
	  $query = mysqli_query($con, "SELECT u.email from transactions t JOIN user_accounts u ON t.user_id=u.id WHERE t.trans_id = $trans_id");
	  $ret = mysqli_fetch_array($query);
	  $user_email = $ret['email'];
	  $sql = "UPDATE transactions SET trans_status=3, trans_remarks =?, trans_process_date=current_timestamp(), trans_process_by=? WHERE trans_id=?";
	  $stmt = mysqli_prepare($con, $sql);
	  mysqli_stmt_bind_param($stmt, "ssi", $remarks, $username, $trans_id);
	  if (mysqli_stmt_execute($stmt)) {
	      //function to send email
						$subject = "Regarding your Admission to ACTS Computer College.";
						$message = "We regret to inform you that your application for admission to ACTS Computer College has been rejected. Log in to your ACTS Web Portal account for more information.";
						sendEmail($subject,$message,$user_email);
	     echo "<script type='text/javascript'>document.location ='Transactions.php?sta=3';</script>";
	  } else {
	      // Display an error message if the update fails
	      echo '<script>alert("Error occured! Try again later");</script>';
	  }
	  mysqli_stmt_close($stmt);
	}
?>
	<main>
		<div class="title-div flex gap">
			<div>
		 		 <h5 class="title" id="main-title">Payment Transactions:</h5>
			</div>
		</div>
    <form method="post" id="filters">
      <div id="form_div">
      	<div class="filter-div">
			    <label>Mode: </label>
			    <select name="mode" id="mode" onchange="this.form.submit()">
				    <option  <?php if(isset($_POST['mode']) && $_POST['mode'] == 'All') echo 'selected';?>>All</option>
				    <option  <?php if(isset($_POST['mode']) && $_POST['mode'] == 'Cashier') echo 'selected';?>>Cashier</option>
				    <option  <?php if(isset($_POST['mode']) && $_POST['mode'] == 'GCash to GCash') echo 'selected';?>>GCash to GCash</option>
				    <option  <?php if(isset($_POST['mode']) && $_POST['mode'] == 'GCash to China Bank') echo 'selected';?>>GCash to China Bank</option>
				    <option  <?php if(isset($_POST['mode']) && $_POST['mode'] == 'Bank to Bank Transfer') echo 'selected';?>>Bank to Bank Transfer</option>
			    </select>
      	</div>
		  	<div class="filter-div">
			    <label>Status: </label>
			     	<select id="status" name="status" onchange="this.form.submit()">
			      	<option <?php if(isset($_POST['status']) && $_POST['status'] == 'All') echo 'selected';?>> All </option>
				      <option value="1"<?php if(isset($_POST['status']) && $_POST['status'] == 1) echo 'selected';?>>Pending</option>
				      <option value="2"<?php if(isset($_POST['status']) && $_POST['status'] == 2) echo 'selected';?>> Approved </option>
				      <option value="3"<?php if(isset($_POST['status']) && $_POST['status'] == 3) echo 'selected';?>> Rejected </option>
			      </select>
		    </div>
      </div>
    </form>
    <div id="tables_div">
			<table id="tbl"  class="table table-hover">
				<thead class="table-success">
					<tr>
					<th>Name</th>
					<th>Reference</th>
					<th>Mode</th>
					<th>Sender</th>
					<th>Date</th>
					<th>Amount</th>
					<th>Purpose</th>
					<th>Status</th>
					<th>Process Date</th>
					<th>Processed By</th>
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
		<div class="modal fade" id="approve_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Are you sure to Approve Transaction?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="modal-form" method="post">
            <div class="modal-footer">
              <input type="hidden" name="u_id" id="u_id">
              <input type="hidden" name="t_id" id="t_id">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
              <button id="approve" name="approve" type="submit" class="btn btn-success">Yes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="reject_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Reject Transaction</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="modal-form" method="post">
            <div class="modal-body">
              <div class="mb-3">
                <input type="hidden" id="r_tid" name="r_tid">
                <label for="modal-fid" class="form-label">Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
              <button id="reject" name="reject" type="submit" class="btn btn-success">Confirm</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="remarks_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
           	<h5 class="modal-title" id="myModalLabel">Remarks</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
             	<textarea name="remarks_txt" id="remarks_txt" class="form-control" rows="3" disabled></textarea>
            </div>
          </div>
          <form id="modal-form" method="post">
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
	            <button id="view_proof" type="submit" class="btn btn-primary">View Proof</button>
            </div>
          </form>
        </div>
      </div>
    </div>
	</main>
	<script type="text/javascript">
		//data table declaration
		$(document).ready(function() {
			$('#tbl').DataTable({
				"columnDefs": [
          { "orderable": false, "targets": [3,9] }
        ],
				dom: '<"top"<"layer1"f><"layer1"<"my-custom-element">><"layer2"lB>>t<"bottom"i>p<"clear">',
				buttons: [
					'copy', 'csv', 'excel', 'pdf', 'print'
				],
			  order: [[4, 'desc']],
				initComplete: function() {
				  $('.my-custom-element').prepend($('#filters'));
				}
			});

			// set id of transaction to be approve
			$(document).on("click", ".approve_button", function(){
				var uid = $(this).attr("data-uid");
				var tid = $(this).attr("data-tid");
				$("#u_id").val(uid);
				$("#t_id").val(tid);
			});

			// set id of transaction to be rejected
			$(document).on("click", ".reject_button", function(){
				var tid = $(this).attr("data-tid");
				$("#r_tid").val(tid);
			});

			$(document).on("click", ".view_remarks", function(){
			  var remarks = $(this).attr("data-remarks");
			  var url = $(this).attr("data-url");
			  $("#remarks_txt").val(remarks);
			  $("#view_proof").attr('data-url', url);
			});

			$('#view_proof').click(function(e) {
			  e.preventDefault();
			  var view_url = $(this).attr('data-url');
			  window.open(view_url, '_blank');
			});
		});
	</script>
</body>
</html>
<style type="text/css">
	.filter-div{
		width: 100%;
	}
	.btn-div{
		text-align: right;
	}
</style>
