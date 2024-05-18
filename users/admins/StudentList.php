<?php 
session_start();
error_reporting(0);

//navigation styles
$dmstyle = "active";

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
	<title>ACTS Web Portal | Students List</title>
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
//include navigationbar
include('NavigationBar.php');

	function showTable($grade_year, $course_strand, $status) {
    global $table_contents, $con;
    $table_contents = "";
    $where_conditions = array();

    if ($grade_year !== 'All') {
        $where_conditions[] = "s.student_year = '$grade_year'";
    }

    if ($course_strand !== 'All') {
        $where_conditions[] = "s.student_course = '$course_strand'";
    }

    if ($status !== 'All') {
        $where_conditions[] = "s.enrolled_status = '$status'";
    }

    if (!empty($where_conditions)) {
        $sql = "SELECT s.*, a.app_id, a.fname, a.mname, a.lname, a.phone, u.email, c.acronym, ss.section_id, sec.name FROM student_list s LEFT OUTER JOIN applications a ON s.user_id=a.user_id LEFT OUTER JOIN user_accounts u ON s.user_id = u.id LEFT OUTER JOIN course_strand c ON s.student_course=c.id LEFT OUTER JOIN student_section ss ON s.student_num = ss.student_num LEFT OUTER JOIN sections sec ON ss.section_id = sec.section_id WHERE " . implode(' AND ', $where_conditions);
    } else {
        $sql = "SELECT s.*, a.app_id, a.fname, a.mname, a.lname, a.phone, u.email, c.acronym, ss.section_id, sec.name FROM student_list s LEFT OUTER JOIN applications a ON s.user_id=a.user_id LEFT OUTER JOIN user_accounts u ON s.user_id = u.id LEFT OUTER JOIN course_strand c ON s.student_course=c.id LEFT OUTER JOIN student_section ss ON s.student_num = ss.student_num LEFT OUTER JOIN sections sec ON ss.section_id = sec.section_id;";
    }

    // execute SQL query and generate table contents
	  $result=$con->query($sql);
		while ($row = $result->fetch_assoc()) {
    		$view_button = '<button  type="button" class="view-button btn btn-primary" data-href="AdmissionForm.php?id='.$row["app_id"].'"><i class="las la-search"></i></button>';
				$view_button = str_replace("\r\n", "", $view_button); // remove new lines

				$userId = $row['user_id'];
	 			$table_contents = $table_contents."<tr>
				<td>" .$row["student_num"] ."</td>
				<td>" .$row["lname"].", ".$row["fname"]." ".strtoupper(substr($row['mname'],0,1))."</td>
				<td>" .$row["student_year"] ."</td>
				<td>" .$row["acronym"] ."</td>
				<td> ".$row["name"] ."</td>
				<td>" .$row["email"] ."</td>
				<td>" .$row["phone"] ."</td>
				<td>" .$view_button.$delete_button."</td>
			</tr>";
		}
	}

showTable('All','All', 1);

if(isset($_POST['grade_year'])||isset($_POST['course_strand'])||isset($_POST['section'])||isset($_POST['status'])){
	$grade_year = ($_POST['grade_year']);
	$course_strand = ($_POST['course_strand']);
	$status = ($_POST['status']);
	showTable($grade_year,$course_strand,$status);
}
	
?>
	<main>
		<div class="title-div flex gap">
		<div>
	 		 <h5 class="title" id="main-title">Student List:</h5>
		</div>
	</div>
      			<form method="post" id="filters">
      				<div id="form_div">
      					<div class="filter-div">
			      			<label>Year: </label>
			      			<select name="grade_year" id="grade_year" onchange="this.form.submit()">
				      			<option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == 'All') echo 'selected';?>>All</option>
				                <option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == '11') echo 'selected';?>>11</option>
				                <option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == '12') echo 'selected';?>>12</option>
				                <option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == '1') echo 'selected';?>>1</option>
				                <option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == '2') echo 'selected';?>>2</option>
				                <option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == '3') echo 'selected';?>>3</option>
				                <option  <?php if(isset($_POST['grade_year']) && $_POST['grade_year'] == '4') echo 'selected';?>>4</option>
			      			</select>
      					</div>
		      			<div class="filter-div">
			      			<label>Course: </label>
			      			<select id="course_strand" name="course_strand" onchange="this.form.submit()">
				                <option <?php if(isset($_POST['course_strand']) && $_POST['course_strand'] == 'All') echo 'selected';?>>All</option>
				                <?php
				                $query=mysqli_query($con,"SELECT * FROM course_strand WHERE type = 1");
				                  	while($row=mysqli_fetch_array($query)){
				                ?>
				                <option class = "strandops" value="<?php echo $row['id'] ?>" <?php if(isset($_POST['course_strand']) && $_POST['course_strand'] == $row['id']) echo 'selected';?>> <?php echo $row['acronym'];?></option>
				                <?php } 
				                $query=mysqli_query($con,"SELECT * FROM course_strand WHERE type = 2");
				                  	while($row=mysqli_fetch_array($query)){
				                ?>    
				                <option class = "courseops" value="<?php echo $row['id'] ?>" <?php if(isset($_POST['course_strand']) && $_POST['course_strand'] == $row['id']) echo 'selected';?>> <?php echo $row['acronym'];?></option>
				                <?php } ?>  
			             	</select>
		             	</div>
		             	<div class="filter-div">
			      			<label>Status: </label>
			      			<select id="status" name="status" onchange="this.form.submit()">
				                <option value="1"<?php if(isset($_POST['status']) && $_POST['status'] == 1) echo 'selected';?>>Enrolled</option>
				                <option value="0"<?php if(isset($_POST['status']) && $_POST['status'] == 0) echo 'selected';?>> Not Enrolled </option>
				                <option <?php if(isset($_POST['status']) && $_POST['status'] == 'All') echo 'selected';?>> All </option>
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
					<th>Year</th>
					<th>Course</th>
					<th>Section</th>
					<th>Email</th>
					<th>Phone</th>
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
		<script type="text/javascript">
			$(document).ready(function() {

				$('#tbl').DataTable({
					"columnDefs": [{
					"orderable": false,
					"targets": [5,6]
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

			//filter select options

function filterOptions() {
  const grade_year = document.getElementById("grade_year").value;
  
  const strandops = document.getElementsByClassName("strandops");
  const courseops = document.getElementsByClassName("courseops");
  
  for (let i = 0; i < strandops.length; i++) {
    strandops[i].style.display = "none";
  }
  
  for (let i = 0; i < courseops.length; i++) {
    courseops[i].style.display = "none";
  }
  
  if (grade_year == "All") {
    showElements(strandops);
    showElements(courseops);
  } else if (grade_year == "11" || grade_year == "12") {
    showElements(strandops);
    course_strand.selectedIndex = 0; // set selected index to 0
  } else {
    showElements(courseops);
    course_strand.selectedIndex = 0; // set selected index to 0
  }
}

function showElements(elements) {
  for (let i = 0; i < elements.length; i++) {
    elements[i].style.display = "block";
  }
}
filterOptions();

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

</style>
