<?php 
session_start();
error_reporting(0);

//navigation styles
$astyle = "active";

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
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | Admissions</title>
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

	 	function showTable($category, $status){
	 		//show data on datatable based on category and status filter
	 		global $table_contents, $con;
	 		$table_contents = "";
	 		$where_conditions = array();

	 		if ($category !== 'All') {
	 			// if category not all, add where condition
	        	$where_conditions[] = "category = $category";
	    	}

		    if ($status !== 'All') {
		    	// if status not all, add where condition
		        $where_conditions[] = "app_status = $status";
		    }

		    if (!empty($where_conditions)) {
		    	// add where conditions
		    	$sql = "SELECT a.*, c.acronym FROM applications a LEFT OUTER JOIN course_strand c ON a.course = c.id WHERE " . implode(' AND ', $where_conditions);
		    } else {
		    	$sql = "SELECT a.*, c.acronym FROM applications a LEFT OUTER JOIN course_strand c ON a.course = c.id";
		    }

		    // execute query and generate table contents
	 		$result=$con->query($sql);
			while ($row = $result->fetch_assoc()) {
					$app_id = $row['app_id'];

					// convert category to text
					$cat = ($row['category'] == 1) ? "New Student" : 
	            				(($row['category'] == 2) ? "Transferee" : null);

	            	//convert status to text
					$app_status = ($row['app_status'] == 1) ? "Pending" : 
					                (($row['app_status'] == 2) ? "Approved" : 
					                (($row['app_status'] == 3) ? "Rejected" : null));

	 				$table_contents = $table_contents."<tr class='clickable-row' data-href='AdmissionForm.php?id=$app_id'>
					<td>" .$row["lname"].", ".$row["fname"]." ".strtoupper(substr($row['mname'],0,1)). "</td>
					<td>" .$row["year"]."-".$row["acronym"]."</td>
					<td>" .$cat."</td>
					<td>" .$row["date_applied"] ."</td>
					<td>" .$app_status."</td>
					<td>" .$row["app_remarks_date"] ."</td>
					<td>" .$row["app_process_by"] ."</td>
				</tr>";
			} 
	 	}

	 	//show datatable data initially
	 	$sta = $_GET['sta'];
	 	showTable('All',$sta);

	 	// if user selects category/status, show filtered filter
		if(isset($_POST['category'])||isset($_POST['status'])){
			$category = $_POST['category'];
			$status = $_POST['status'];
			showTable($category,$status);
		}
	?>

	<main>
		<div>
        	<h5 class="title" id="main-title">Admissions:</h5>  
      	</div>
      	<form method="post" id="filters">
      		<div id="form_div">
		      	<div class="filter-div">	
			     	<label>Category: </label>
			     	<select name="category" id="category" onchange="this.form.submit()">
				    	<option <?php if(isset($_POST['category']) && $_POST['category'] == 'All') echo 'selected';?>>All</option>
				        <option value="1" <?php if(isset($_POST['category']) && $_POST['category'] == 1) echo 'selected';?>>New Student</option>
				        <option value="2" <?php if(isset($_POST['category']) && $_POST['category'] == 2) echo 'selected';?>>Transferee</option>
			     	</select>
		      	</div>
		      	<div class="filter-div">
			      	<label>Status: </label>
			      	<select id="status" name="status" onchange="this.form.submit()">
				        <option <?php if(isset($_POST['status']) && $_POST['status'] == 'All') echo 'selected';?>>All</option>
				        <option value="1" <?php if(isset($_POST['status']) && $_POST['status'] == 1) echo 'selected';?>>Pending</option>
				        <option value="2" <?php if(isset($_POST['status']) && $_POST['status'] == 2) echo 'selected';?>>Approved</option>
				        <option value="3" <?php if(isset($_POST['status']) && $_POST['status'] == 3) echo 'selected';?>>Rejected</option>
			        </select>
			    </div>
	        </div>
	    </form>
      	<div id="tables_div">
			<table id="tbl"  class="table table-hover">
				<thead class="table-success">
					<tr>
					<th>Name</th>
					<th>Year & Course</th>
					<th>Category</th>
					<th>Date Applied</th>
					<th>Status</th>
					<th>Process Date</th>
					<th>Processed By:</th>
					</tr>
				</thead>
				<tbody>
					<?php echo "$table_contents";?>
				</tbody>
			</table>
		</div>
	</main>
	<script type="text/javascript">
		//dataTable with buttons
		$(document).ready(function() {
			$('#tbl').DataTable({
			    dom: '<"top"f><"my-custom-element">lBt<"bottom"i>p<"clear">',
			    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
			    order: [[3, 'desc']],
			  	initComplete: function() {
				  $('.my-custom-element').prepend($('#filters'));
				}
			});
		});

		//make data table clickable
		jQuery(document).ready(function($) {
	    	$(".clickable-row").click(function() {
	     		window.location = $(this).data("href");
	    	});
		});
	</script>
</body>
</html>
<style type="text/css">
	.filter-div{
		width: 100%;
	}
	#tables_div{
		overflow: auto;
	}
</style>