<?php 
session_start();
error_reporting(0);

//navigation styles
$grdstyle = "active";

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
	<title>ACTS Web Portal | Grades </title>
	<!-- bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<!-- styles -->
  	<link rel="stylesheet" href="../../css/navigation.css"/>
  	<link rel="stylesheet" href="../../css/style.css"/>
  	<link rel="stylesheet" href="../../css/DataTables.css"/>
  	<link rel="stylesheet" href="../../css/grades.css"/>
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
$sem_id = $sy_ret['sem_id'];
$sy_id = $sy_ret['sy_id'];

function showTable1($id, $sem, $sy) {
    global $table_contents1, $con;
    $table_contents1 = "";

   $sql = "SELECT cs.sched_id, c.acronym, sec.year, sec.name, sub.subject_code, cs.subject
FROM class_schedule cs 
JOIN subjects sub ON cs.subject = sub.subject_description 
JOIN class_schedule_sections css ON cs.sched_id = css.class_id 
JOIN sections sec ON css.section_id = sec.section_id AND sec.course = sub.sub_course
JOIN course_strand c ON sec.course = c.id 
WHERE cs.faculty_id = ? AND cs.sem_id = ? AND cs.sy_id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("iii", $id, $sem, $sy);
    $stmt->execute();

    $result = $stmt->get_result();
    $processedSections = []; 
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schedule_code = $row["acronym"] . $row["year"] . $row["name"] . ' - ' . $row["subject_code"] . ' ('. $row["subject"]. ')' ;
            if (!in_array($schedule_code, $processedSections)) { 
                $processedSections[] = $schedule_code; 
                $table_contents1 .= "<tr class='viewList clickable-row' data-href='ClassGrades.php?id={$row["sched_id"]}&acronym={$row["acronym"]}&year={$row["year"]}&name={$row["name"]}&subject_code={$row["subject_code"]}&subject={$row["subject"]}'>

                    <td style='text-align: center'>$schedule_code</td>
                </tr>";
            }
        }
    }
}


if(isset($_POST['faculty'])){
    $faculty_id = $_POST['faculty'];
    showTable1($faculty_id, $sem_id, $sy_id);
}
	
	
?>
	<main>

       
		<div>
	 		 <h5 class="title" id="main-title">Grades:</h5>
		</div>
        <form method="post" id="filters">
	  <div>
	    <select name="faculty" id="faculty" class="form-select" onchange="this.form.submit()">
		    <option hidden>Select Faculty</option>
		    <?php
		    // Fetch faculty options from the database
		    $sql = "SELECT id, prefix, fname, mname, lname FROM faculty";
		    $result = $con->query($sql);
		    // Generate <option> elements
		    if ($result->num_rows > 0) {
		        while ($row = $result->fetch_assoc()) {
		            $fac_id = $row['id'];
		            $fac_name = $row['prefix']." ".$row['fname']." ".substr($row['mname'], 0, 1)." ".$row['lname'];
		            $selected = ($_POST['faculty'] == $fac_id) ? "selected" : ""; // Check if the option is selected
		            echo "<option value='$fac_id' $selected>$fac_name</option>";
		        }
		    } ?>
			</select>
	  </div>
	</form>

	<div id="tables_div">
	  <table id="tbl" class="table table-hover">
	    <thead>
	    	<th></th>
	    </thead>
	    <tbody class="table-success">
	      <?php echo $table_contents1; ?>
	    </tbody>
	  </table>
	</div>
	</main>







	<script type="text/javascript">
		$(document).ready(function() {
			const sem_id = <?php echo $sem_id ?>;
			const sy_id = <?php echo $sy_id ?>;

			// Attach the click event listener to the table and handle clicks on the edit button
					$('table').on('click', '.viewList', function() {
						const class_id = $(this).data('id');
						$.ajax({
								url: 'get_class_list.php',
								method: 'POST',
								data: { class_id: class_id },
								dataType: 'json',
								success: function(response) {
									// Handle the response from the server
									if (response.status === 'success') {
										
									} else {
										// Handle error case
										console.log(response.message);
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
					//make data table clickable
				jQuery(document).ready(function($) {
					$(".clickable-row").click(function() {
						window.location = $(this).data("href");
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
	.btn-div{
		text-align: right;
	}

</style>
