<?php 
session_start();
error_reporting(0);

	//navigation styles
	$drstyle = "active";

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
	<title>ACTS Web Portal | Document Requests</title>
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

 	function showTable($typ, $stat){
 		global $table_contents, $con;
 		$table_contents = "";
 		$where_conditions = array();

	    if ($typ !== 'All') {
		    if ($typ === 'Others') {
		        $where_conditions[] = "r.doc_type NOT IN ('Copy of Grades', 'Transcript of Records (TOR)', 'Good Moral Certificate', 'Honorable Dismissal')";
		    } else {
		        $where_conditions[] = "r.doc_type = '$typ'";
		    }
			}

	    if ($stat !== 'All') {
	        $where_conditions[] = "r.req_status = '$stat'";
	    }

	    if (!empty($where_conditions)) {
	        $sql = "SELECT s.student_num, a.fname, a.mname, a.lname, r.* FROM student_list s JOIN applications a ON s.user_id = a.user_id JOIN requests r ON s.user_id = r.user_id WHERE " . implode(' AND ', $where_conditions);
	    } else {
	        $sql = "SELECT s.student_num, a.fname, a.mname, a.lname, r.* FROM student_list s JOIN applications a ON s.user_id = a.user_id JOIN requests r ON s.user_id = r.user_id";
	    }
 		
 		 // execute SQL query and generate table contents
	 $result = $con->query($sql);
		while ($row = $result->fetch_assoc()) {
			$req_id = $row['req_id'];
			$remarks = $row['req_remarks'];

				$view_remarks = '<button  type="button" class="view_remarks btn btn-danger" data-bs-toggle="modal" data-bs-target="#remarks_modal" data-remarks="'.$remarks.'"><i class="las la-search"></i></button>';
    			$view_remarks = str_replace("\r\n", "", $view_remarks); // remove new lines

				$done_button = '<button type="button" class="done_button btn btn-primary" data-id="'.$req_id.'" data-bs-toggle="modal" data-bs-target="#done_modal"><i class="las la-check"></i></button>';
	    		$done_button = str_replace("\r\n", "", $done_button); // remove new lines

				$approve_button = '<button type="button" class="approve_button btn btn-success" data-bs-toggle="modal" data-bs-target="#approve_modal" data-id="'.$req_id.'"><i class="las la-check"></i></button>';
	    		$approve_button = str_replace("\r\n", "", $approve_button); // remove new lines

	    		$reject_button = '<button type="button" class="reject_button btn btn-danger" data-bs-toggle="modal" data-bs-target="#reject_modal" data-id="'.$req_id.'"><i class="las la-times"></i></button>';
	    		$reject_button = str_replace("\r\n", "", $reject_button); // remove new lines

	    		//convert status to text
          $req_status = ($row['req_status'] == 1) ? "Pending" : 
                        (($row['req_status'] == 2) ? "Processing" : 
                        (($row['req_status'] == 3) ? "Rejected" :
                        (($row['req_status'] == 4) ? "Fulfilled" : null)));

 				$table_contents = $table_contents."<tr>
				<td>" .$row["student_num"] ."</td>
				<td>" .$row["lname"].", ".$row["fname"]. " ".$row["mname"]."</td>
				<td>" .$row["doc_type"] ."</td>
				<td>" .$row["copies"] ."</td>
				<td>" .$row["purpose"] ."</td>
				<td>" .date('M d, Y', strtotime($row["req_date"]))."</td>
				<td>" .$req_status ."</td>
				<td>" .(($row['pickup_date'] != null) ? date('F d, Y', strtotime($row['pickup_date'])) : '') . "</td>
				<td>" .(($row['req_process_date'] != null) ? date('F d, Y', strtotime($row['req_process_date'])) : '') . "</td>
				<td>" .$row["req_process_by"] ."</td>";
			if ($req_status == "Pending") {
			    $table_contents =  $table_contents."
			    <td>".$approve_button.$reject_button."</td>
				</tr>";
			} else if ($req_status == "Processing"){
			  	$table_contents =  $table_contents."
		    				<td>" . $done_button . "</td>
		        </tr>";
			} else if ($req_status == "Rejected") {
				$table_contents =  $table_contents."
			    <td>".$view_remarks."</td>
				</tr>";
			} else {
			    $table_contents =  $table_contents."
			    <td></td></tr>";
			}
				
		} 
 	}

 $status = $_GET['sta'];
 	showTable('All',$status);

if(isset($_POST['doc_type'])||isset($_POST['status'])){
	$doc_type = $_POST['doc_type'];
	$status = $_POST['status'];
	showTable($doc_type,$status);
}

if (isset($_POST['approve'])) {
  $id = $_POST['a_id'];
  $pickup = $_POST['pickup'];
  $sql = "UPDATE requests SET req_status = 2, pickup_date=?, req_process_date=current_timestamp(), req_process_by=? WHERE req_id=?";
  $stmt = mysqli_prepare($con, $sql);
  mysqli_stmt_bind_param($stmt, "ssi", $pickup, $username, $id);
  if (mysqli_stmt_execute($stmt)) {
     echo "<script type='text/javascript'>document.location ='DocumentRequests.php?sta=2';</script>";
  } else {
      // Display an error message if the faculty ID update fails
      echo $error_alert;
  }
  mysqli_stmt_close($stmt);
}


if (isset($_POST['reject'])) {
  $id = $_POST['r_id'];
  $remarks = $_POST['remarks'];
  $sql = "UPDATE requests SET req_status = 3, req_remarks=?, req_process_date=current_timestamp(), req_process_by=? WHERE req_id=?";
  $stmt = mysqli_prepare($con, $sql);
  mysqli_stmt_bind_param($stmt, "ssi", $remarks, $username, $id);
  if (mysqli_stmt_execute($stmt)) {
     echo "<script type='text/javascript'>document.location ='DocumentRequests.php?sta=3';</script>";
  } else {
      // Display an error message if the faculty ID update fails
      echo $error_alert;
  }
  mysqli_stmt_close($stmt);
}

if (isset($_POST['done'])) {
  $id = $_POST['d_id'];
  $sql = "UPDATE requests SET req_status = 4, req_process_date=current_timestamp(), req_process_by=? WHERE req_id=?";
  $stmt = mysqli_prepare($con, $sql);
  mysqli_stmt_bind_param($stmt, "si", $username, $id);
  if (mysqli_stmt_execute($stmt)) {
     echo "<script type='text/javascript'>document.location ='DocumentRequests.php?sta=4';</script>";
  } else {
      // Display an error message if the faculty ID update fails
      echo $error_alert;
  }
  mysqli_stmt_close($stmt);
}
?>
	<main>
		<div>
        	<h5 class="title" id="main-title">Document Requests:</h5>  
      	</div>
      			<form method="post" id="form">
      				<div id="form_div">
		      			<div class="filter-div">	
			      			<label>Document Type: </label>
			      			<select name="doc_type" id="doc_type" onchange="this.form.submit()">
				      			<option <?php if(isset($_POST['doc_type']) && $_POST['doc_type'] == 'All') echo 'selected';?>>All</option>
				                <option <?php if(isset($_POST['doc_type']) && $_POST['doc_type'] == 'Copy of Grades') echo 'selected';?>>Copy of Grades</option>
				                <option <?php if(isset($_POST['doc_type']) && $_POST['doc_type'] == 'Transcript of Records (TOR)') echo 'selected';?>>Transcript of Records (TOR)</option>
				                <option <?php if(isset($_POST['doc_type']) && $_POST['doc_type'] == 'Good Moral Certificate') echo 'selected';?>>Good Moral Certificate</option>
				                <option <?php if(isset($_POST['doc_type']) && $_POST['doc_type'] == 'Honorable Dismissal') echo 'selected';?>>Honorable Dismissal</option>
				                <option <?php if(isset($_POST['doc_type']) && $_POST['doc_type'] == 'Others') echo 'selected';?>>Others</option>
			      			</select>
		      			</div>
		      			<div class="filter-div">
			      			<label>Status: </label>
			      			<select id="status" name="status" onchange="this.form.submit()">
				                <option <?php if(isset($_POST['status']) && $_POST['status'] == 'All') echo 'selected';?>>All</option>
				                <option value="1" <?php if(isset($_POST['status']) && $_POST['status'] == 1) echo 'selected';?>>Pending</option>
				                <option value="2" <?php if(isset($_POST['status']) && $_POST['status'] == 2) echo 'selected';?>>Processing</option>
				                <option value="3" <?php if(isset($_POST['status']) && $_POST['status'] == 3) echo 'selected';?>>Rejected</option>
				                <option value="4" <?php if(isset($_POST['status']) && $_POST['status'] == 4) echo 'selected';?>>Fulfilled</option>
			             	</select>
		             	</div>
             		</div>
             	</form>
        <div id="tables_div">
			<table id="tbl"  class="table table-hover">
				<thead class="table-success">
					<tr>
					<th>Number</th>
					<th>Name</th>
					<th>Request</th>
					<th>No. of Copies</th>
					<th>Purpose</th>
					<th>Date Requested</th>
					<th>Status</th>
					<th>Pick Up Date</th>
					<th>Process Date</th>
					<th>Processed by:</th>
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
                <h5 class="modal-title" id="myModalLabel">Approve Document Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="modal-form" method="post">
              	<div class="modal-body">
                  <div class="mb-3">
                	<input type="hidden" name="a_id" id="a_id">
                    <label for="modal-fid" class="form-label">Set Pickup Date</label>
                    <input type="date" name="pickup" id="pickup" min="<?php echo date('Y-m-d', strtotime('+7 days')) ?>" required>
                  </div>
                </div>
                <div class="modal-footer">
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
                <h5 class="modal-title" id="myModalLabel">Reject Document Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="modal-form" method="post">
                <div class="modal-body">
                  <div class="mb-3">
                  	<input type="hidden" id="r_id" name="r_id">
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
        <div class="modal fade" id="done_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Mark the Request as Fulfilled?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="modal-form" method="post">
                <div class="modal-footer">
                  <input type="hidden" id="d_id" name="d_id">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                  <button id="done" name="done" type="submit" class="btn btn-success">Yes</button>
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
            </div>
          </div>
        </div>
	</main>
	<script type="text/javascript">
			//dataTable with buttons
			$(document).ready(function() {
			  	$('#tbl').DataTable({
			  		"columnDefs": [
            			{ "orderable": false, "targets": [9] }
        			],
			    	dom: '<"top"f><"my-custom-element">lBt<"bottom"i>p<"clear">',
			    	buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
			    	initComplete: function() {
			     		$('.my-custom-element').prepend($('#form'));
			    	},
			    	order: [[5, 'desc']]
			  	});

			  	$(document).on("click", ".approve_button", function(){
				    var id = $(this).attr("data-id");
				    $('#pickup').val("");
				    $("#a_id").val(id);
				});
				$(document).on("click", ".reject_button", function(){
				    var id = $(this).attr("data-id");
				    $("#r_id").val(id);
				});
				$(document).on("click", ".done_button", function(){
				    var id = $(this).attr("data-id");
				    $("#d_id").val(id);
				});
				$(document).on("click", ".view_remarks", function(){
				    var remarks = $(this).attr("data-remarks");
				    $("#remarks_txt").val(remarks);
				});
			});

	</script>
</body>
</html>
<style>
	.filter-div{
		width: 100%;
	}</style>