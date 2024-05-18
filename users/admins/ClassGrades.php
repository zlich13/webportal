    <?php  
     
    session_start(); 
     
    error_reporting(0); 
     
    //navigation styles 
    $grdstyle = "active"; 
     
    //connect to database 
    include('../../dbconnection.php'); 
    
    // Check if upload has been confirmed in a previous request
    if (isset($_SESSION['uploadConfirmed']) && $_SESSION['uploadConfirmed'] === true) {
        // Unset the confirmUpload POST variable to prevent re-execution of confirmation code
        unset($_POST['confirmUpload']);
        // Unset the session variable to reset the confirmation status
        unset($_SESSION['uploadConfirmed']);
    }
     
    // Redirect to logout page if no user logged in 
    if (empty($_SESSION['uid'])) { 
        header('Location: ../../logout.php'); 
        exit; 
    } 
     
     
    $sched_id = $_GET['id']; 
    $acronym = urldecode($_GET['acronym']); 
    $year = urldecode($_GET['year']); 
    $name = urldecode($_GET['name']); 
    $subject_code = urldecode($_GET['subject_code']); 
    $subject = urldecode($_GET['subject']); 
     
    $main_title = "{$acronym}{$year}{$name} — {$subject_code} ({$subject})"; 
     
     
     
    ?> 
     
    <!DOCTYPE html> 
    <html> 
    <head> 
    	<meta charset="utf-8"> 
    	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1"> 
    	<title>ACTS Web Portal | Grades List </title> 
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
    include('NavigationBar.php'); 
    $sem_id = $sy_ret['sem_id']; 
    $sy_id = $sy_ret['sy_id']; 
    
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file-upload']) && $_FILES['file-upload']['error'] === UPLOAD_ERR_OK) { 
        
         // Include PhpSpreadsheet classes 
         require_once '../../PhpSpreadsheet-1.28.0/vendor/autoload.php'; 
      echo "awsdaw";
         $uploadFile = $_FILES['file-upload']['tmp_name']; 
     if (isset($_POST['confirmUpload']) && $_POST['confirmUpload'] == 'confirm') {    
         
         try { 
                 
             // Read the spreadsheet 
             $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($uploadFile); 
              
             // Get the first worksheet 
             $worksheet = $spreadsheet->getActiveSheet(); 
              
             // Get the highest row and column numbers used in the worksheet 
             $highestRow = $worksheet->getHighestRow()-3; 
             $highestColumn = 'J'; 
              
             // Loop through each row of the worksheet 
             for ($row = 12; $row <= $highestRow; $row++) { 
                  
                 // Read a row of data into an array 
                 $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE); 
      
                 $name = strtoupper($rowData[0][2]); 
                 $prelim = $rowData[0][3]; 
                 $midterm = $rowData[0][4]; 
                 $prefinal = $rowData[0][5]; 
                 $final = $rowData[0][6]; 
                 $finalGrade = $rowData[0][7]; 
                 $scale = $rowData[0][8]; 
                 $remarks = $rowData[0][9]; 
      
                 $sql = "SELECT student_num FROM student_list sl JOIN applications a ON a.user_id = sl.user_id WHERE CONCAT(UPPER(a.lname), ', ', UPPER(a.fname), COALESCE(CONCAT(' ', LEFT(UPPER(a.mname), 1), '.'), '')) = '$name'"; 
                 // Get the student_num from the database 
                 $result = mysqli_query($con, $sql); 
                 if (mysqli_num_rows($result) == 0) { 
                     $studentNum = null; 
                 } 
                 else { 
                     $ret = mysqli_fetch_assoc($result); 
                     $studentNum = $ret['student_num']; 
      
                     $sql = "SELECT COUNT(*) as count FROM grades WHERE cs_id = $sched_id AND student_num = $studentNum"; 
                     $result = mysqli_query($con, $sql); 
      
                     $rowCount = 0; 
                     if (mysqli_num_rows($result) !== 0) { 
                          $ret = mysqli_fetch_assoc($result); 
                         $rowCount = $ret['count']; 
                     } 
                     $rowCount = (int)$rowCount;
                     
                     if ($rowCount === 0) { 
                         // Insert the data into the database 
                         $stmt = $con->prepare("INSERT INTO grades (cs_id, student_num, prelim, midterm, prefinal, final, final_grade, scale, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"); 
                         $stmt->bind_param("iiiiiiids", $sched_id, $studentNum, $prelim, $midterm, $prefinal, $final, $finalGrade, $scale, $remarks); 
                     } else { 
                         
                         // Update the data from the database 
                         $stmt = $con->prepare("UPDATE grades SET prelim = ?, midterm = ?, prefinal = ?, final = ?, final_grade = ?, scale = ?, remarks = ? WHERE cs_id = ? AND student_num = ?"); 
                         $stmt->bind_param("iiiiidsii", $prelim, $midterm, $prefinal, $final, $finalGrade, $scale, $remarks, $sched_id, $studentNum); 
                     } 
      
                     // Execute the statement and check for errors 
                     if (!$stmt->execute()) { 
                         echo "<script>alert('Error: " . $stmt->error . "');</script>"; 
                     } 
                 } 
            
                  
             } 
             
              $_SESSION['uploadConfirmed'] = true;
              
        
         } catch (Exception $e) { 
             echo 'Error loading file: ',  $e->getMessage(), "\n"; 
              echo "<script>alert('File Upload Failed! Please check the excel file correctly');</script>";
         } 
     
     }  
          
     } 
      
     
    function showTable($con, &$table_contents, $sem, $sy)
{
    $table_contents = ''; // Initialize the variable

 $sql = "SELECT
    CONCAT(app.lname, ', ', app.fname, ' ', LEFT(app.mname, 1)) AS student_name, 
    cs.subject, 
    COALESCE(NULLIF(g.prelim, 0), 'N/A') AS prelim, 
    COALESCE(NULLIF(g.midterm, 0), 'N/A') AS midterm, 
    COALESCE(NULLIF(g.prefinal, 0), 'N/A') AS prefinal, 
    COALESCE(NULLIF(g.final, 0), 'N/A') AS final, 
    CASE WHEN g.prelim IS NULL OR g.prelim = 0 OR g.midterm IS NULL OR g.midterm = 0 OR g.prefinal IS NULL OR g.prefinal = 0 OR g.final IS NULL OR g.final = 0 THEN 'N/A' ELSE COALESCE(g.final_grade, 'N/A') END AS final_grade, 
    CASE WHEN g.prelim IS NULL OR g.prelim = 0 OR g.midterm IS NULL OR g.midterm = 0 OR g.prefinal IS NULL OR g.prefinal = 0 OR g.final IS NULL OR g.final = 0 THEN 'N/A' ELSE COALESCE(g.scale, 'N/A') END AS scale, 
    CASE WHEN g.prelim IS NULL OR g.prelim = 0 OR g.midterm IS NULL OR g.midterm = 0 OR g.prefinal IS NULL OR g.prefinal = 0 OR g.final IS NULL OR g.final = 0 THEN 'N/A' ELSE COALESCE(g.remarks, 'N/A') END AS remarks, 
    ss.student_num, g.id AS grade_id -- Include the ID from grades table
FROM 
    class_schedule cs 
JOIN 
    class_schedule_sections css ON cs.sched_id = css.class_id 
JOIN 
    sections sec ON css.section_id = sec.section_id 
JOIN 
    subjects sub ON cs.subject = sub.subject_description 
JOIN 
    course_strand c ON sec.course = c.id 
JOIN 
    student_subjects ss ON ss.subject_id = sub.subject_id AND ss.section_id = sec.section_id 
JOIN 
    student_list sl ON ss.student_num = sl.student_num 
JOIN 
    applications app ON sl.user_id = app.user_id 
LEFT JOIN 
    (SELECT cs_id, student_num, MAX(id) AS max_id
     FROM grades
     GROUP BY cs_id, student_num) max_grades ON cs.sched_id = max_grades.cs_id AND ss.student_num = max_grades.student_num 
LEFT JOIN 
    grades g ON max_grades.max_id = g.id
WHERE 
    cs.sched_id = ? 
    AND cs.sem_id = ? 
    AND cs.sy_id = ? 
ORDER BY student_name ASC";



    $stmt = $con->prepare($sql);
    $stmt->bind_param("iii", $_GET['id'], $sem, $sy);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $counter = 1;
        while ($row = $result->fetch_assoc()) {
            $prelim = $row['prelim'];
            $midterm = $row['midterm'];
            $prefinal = $row['prefinal'];
            $final = $row['final'];
            $final_grade = $row['final_grade'];
            $scale = $row['scale'];
            $remarks = $row['remarks'];

            $gradeIsNullZero = $prelim === null || $prelim === 0 ||
                $midterm === null || $midterm === 0 ||
                $prefinal === null || $prefinal === 0 ||
                $final === null || $final === 0;

            $final_grade = $gradeIsNullZero ? 'N/A' : $final_grade;
            $scale = $gradeIsNullZero ? 'N/A' : $scale;
            $remarks = $gradeIsNullZero ? 'N/A' : $remarks;

            $table_contents .= "<tr>
                        <td>$counter</td>
                        <td>{$row['student_name']}</td>
                        <td>$prelim</td>
                        <td>$midterm</td>
                        <td>$prefinal</td>
                        <td>$final</td>
                        <td>$final_grade</td>
                        <td>";

            if ($scale != 'N/A') {
                $table_contents .= number_format($scale, 2);
            } else {
                $table_contents .= 'N/A';
            }
           
            $table_contents .= "</td>
                        <td>$remarks</td>
                        <td>
                            <input type='hidden' class='student-name' value='{$row['student_name']}' />
                           <button type='button' id='idnum' class='btn btn-primary edit-button' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['student_num']}-{$row['grade_id']}'>Edit</button>
                        </td>
                    </tr>";
            $counter++;
        }
    }
}

     
    // Usage 
    showTable($con, $table_contents,$sem_id,$sy_id); 
    ?> 
     
    <main> 
    <div class="alert alert-success" id="successAlert" style="display:none;"> 
        Grades successfully edited. 
    </div> 
     
     
     
    <div class="modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true"> 
        <div class="modal-dialog"> 
            <div class="modal-content"> 
                <div class="modal-header"> 
                    <h5 class="modal-title" id="editModalLabel">Edit Grades</h5> 
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div> 
                <div class="modal-body"> 
    				<h6 id="editName" style=" font-weight: bold;">Name: </h3> 
     
                    <form id="editForm"> 
                        <input type="hidden" id="gradeId" name="gradeId" value=""> 
                        <input type="hidden" id="studId" name="studId" value=""> 
                        <div class="mb-3"> 
                            <label for="editPrelim" class="form-label">Prelim</label> 
                            <input type="text" class="form-control" id="editPrelim" name="editPrelim"> 
                        </div> 
    					<div class="mb-3"> 
                            <label for="editMidterm" class="form-label">Midterm</label> 
                            <input type="text" class="form-control" id="editMidterm" name="editMidterm"> 
                        </div> 
    					<div class="mb-3"> 
                            <label for="editPrefinal" class="form-label">Prefinal</label> 
                            <input type="text" class="form-control" id="editPrefinal" name="editPrefinal"> 
                        </div> 
    					<div class="mb-3"> 
                            <label for="editFinal" class="form-label">Final</label> 
                            <input type="text" class="form-control" id="editFinal" name="editFinal"> 
                        </div> 
    					 <div class="note-container"> 
                        <p class="note-text">Note: The final grade and scale are automatically calculated by the system.</p> 
                    </div> 
     
                        <button type="submit" class="btn btn-primary">Save Changes</button> 
                    </form> 
                </div> 
            </div> 
        </div> 
    </div> 
     
    <div class="title-div flex gap justify-content-between align-items-center">
        <!-- Back button -->
        <button onclick="goBack()" class="btn btn-primary backbutton">Back to Faculties</button>

        <!-- Main title -->
        <h5 class="title" id="main-title"><?php echo $main_title." Grades" ?></h5>

        <!-- Upload Grades button -->
        <div class="upload-grades-container">
            <button class="btn btn-success" type="submit" id="upload-button" name="upload-button" data-bs-toggle="modal" data-bs-target="#bulkModal">
                <span class="las la-upload" style="margin-right: 3px;"></span>Upload Grades
            </button>
        </div>
    </div>
    
    <form method="post" id="filters" style="display:none"> 
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
        <table id="tbl"  class="table my-table"> 
            <thead class="calibri table-success"> 
                <tr> 
                    <th>#</th> 
                    <th>NAME</th> 
                    <th>PRELIM</th> 
                    <th>MIDTERM</th> 
                    <th>PREFINAL</th> 
                    <th>FINAL</th> 
                    <th>FINAL_GRADE</th> 
                    <th>SCALE</th> 
                    <th>REMARKS</th> 
    				<th>ACTION</th> 
                </tr> 
            </thead> 
            <tbody> 
                <?php echo $table_contents; ?> 
            </tbody> 
        </table> 
    </div> 
     
    <div class="modal" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true"> 
        <div class="modal-dialog"> 
            <div class="modal-content"> 
                <div class="modal-header"> 
                    <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5> 
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div> 
                <div class="modal-body"> 
                    Are you sure you want to upload the Excel grades data? This will override the student grades in the table. 
                </div> 
                <div class="modal-footer"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> 
                    <button type="button" class="btn btn-primary" id="confirmUploadBtn">Confirm Upload</button> 
                </div> 
            </div> 
        </div> 
    </div> 
     
     
    <div class="modal" id="bulkModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"> 
    	<div class="modal-dialog"> 
        	<div class="modal-content"> 
          		<div class="modal-header"> 
           		 	<h5 class="modal-title" id="myModalLabel">Upload Grades</h5> 
           			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          		</div> 
    	      	<div class="modal-body"> 
    	      		<div style="display: flex; justify-content: space-between;"> 
    		      		<div> 
    		      			<a href="../../templates/GradesTemplate.xlsx" download id="template"><span class="las la-download" style="margin-right: 3px"></span>GradesTemplate.xlsx</a> 
    		      		</div> 
    		      		<div> 
    			      	<form method="post" enctype="multipart/form-data"> 
        <label for="file-upload" class="custom-file-upload">Choose File</label> 
        <input id="file-upload" name="file-upload" type="file" style="display: none;"/> 
        <input type="submit" name="confirmUpload" value="Confirm Upload" style="display: none;"/> 
    </form> 
     
    				    </div> 
          			</div> 
    	      	</div> 
    	    </div> 
    	</div> 
    </div> 
     
               </body> 
    		   <script> 
     
        		function openModal() { 
           		 var myModal = new bootstrap.Modal(document.getElementById('bulkModal'), { 
                keyboard: false 
           		 }); 
          			  myModal.show(); 
       			 } 
    		</script> 
             
    		<script type="text/javascript"> 
    			$(document).ready(function() { 
    				 
     
    				$('#tbl').DataTable({ 
    					"columnDefs": [{ 
    					"orderable": false, 
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
    	<script> 
       $(document).ready(function() {
    var studId, gradeId;

    // Handle click on edit button
    $('.edit-button').click(function() {
        var ids = $(this).data('id').split('-'); // Splitting the combined ids
        studId = ids[0]; // Store the student_id globally
        gradeId = ids[1]; // Store the grade_id globally
        var currentCsId = <?php echo $sched_id; ?>;
        var studentName = $(this).closest('tr').find('.student-name').val();
        console.log('Edit button clicked with studID:', studId);
        console.log('Edit button clicked with gradeID:', gradeId);
        console.log('Edit button clicked with csID:', currentCsId);

        // AJAX request to fetch data based on student ID and currentCsId
        $.ajax({
            url: 'fetch_student_grades.php', // Update the URL to your PHP script
            type: 'POST',
            data: { gradeId: gradeId, currentCsId: currentCsId, studentId: studId }, // Send the student ID and currentCsId as data
            success: function(response) {
                var data = JSON.parse(response);
                // Populate the modal input fields with fetched data
                console.log('Response from fetch_student_grades.php:', response);
                $('#editName').text('Name: ' + studentName);
                $('#studId').val(data.student_num);
                $('#gradeId').val(gradeId);
                $('#editPrelim').val(data.prelim);
                $('#editMidterm').val(data.midterm);
                $('#editPrefinal').val(data.prefinal);
                $('#editFinal').val(data.final);
                $('#editFinalGrade').val(data.final_grade);
                $('#editScale').val(data.scale);
                $('#editRemarks').val(data.remarks);
            },
            error: function(xhr, status, error) {
                console.log("Error fetching data:", error);
            }
        });
    });

    // Handle form submission for editing
    $('#editForm').submit(function(e) {
        e.preventDefault();
        var currentCsId = <?php echo $sched_id; ?>;

        // Calculate final grade and scale
        var prelim = parseFloat($('#editPrelim').val()) || 0;
        var midterm = parseFloat($('#editMidterm').val()) || 0;
        var prefinal = parseFloat($('#editPrefinal').val()) || 0;
        var final = parseFloat($('#editFinal').val()) || 0;
        var finalGrade = (prelim + midterm + prefinal + final) / 4;

        // Determine grading scale
        var scale = '';
        if (finalGrade >= 98) scale = '1.0';
        else if (finalGrade >= 95) scale = '1.25';
        else if (finalGrade >= 92) scale = '1.50';
        else if (finalGrade >= 89) scale = '1.75';
        else if (finalGrade >= 86) scale = '2.0';
        else if (finalGrade >= 83) scale = '2.25';
        else if (finalGrade >= 80) scale = '2.50';
        else if (finalGrade >= 77) scale = '2.75';
        else if (finalGrade >= 75) scale = '3.0';
        else scale = '5.0';
        
         // Determine remarks based on final grade
    var remarks = '';
   if(finalGrade >= 75){
        remarks = 'Passed';
    } else if (finalGrade < 75) {
        remarks = 'Failed';
    } else {
        remarks = 'Incomplete';
    }
    console.log(finalGrade);
    console.log(remarks);

        var formData = {
            studId: studId,
            gradeId: gradeId,
            editPrelim: prelim,
            editMidterm: midterm,
            editPrefinal: prefinal,
            editFinal: final,
            editFinalGrade: finalGrade.toFixed(2),
            editScale: scale,
            editRemarks: remarks,
            currentCsId: currentCsId
        };

        console.log('FormData:', formData);
         $('#file-upload').val('');
        // Send AJAX request to update the database with new values
        $.ajax({
            url: 'update_student_grades.php', // Update URL to your PHP script
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log(response.success);
                // Handle success, close modal, update table, etc.
                $('#editModal').modal('hide');
                $("#successAlert").fadeIn();
                fadeOutSuccessAlert();
            },
            error: function(xhr, status, error) {
                console.log("Error updating data:");
                console.log("Status:", xhr.status); // Log the HTTP status code
                console.log("Status Text:", xhr.statusText); // Log the HTTP status text
                console.log("Error:", error); // Log the error message
            }
        });
    });

    function fadeOutSuccessAlert() {
    setTimeout(function() {
        $("#successAlert").fadeOut();
        // Reload the page after fading out the alert
        window.location.replace(window.location.href);
    }, 2000); // 2 seconds delay (2000 milliseconds)
}
});

    </script> 
    
    <script> 
        $(document).ready(function(){ 
          
            var fileUploadConfirmed = true; // Initialize the flag inside the change event
             console.log(fileUploadConfirmed);
           function confirmFileUpload() {
            if (!fileUploadConfirmed) {
                console.log(fileUploadConfirmed);
                if (confirm("Are you sure you want to upload this file? This will override the current grades of similar students.")) {
                    fileUploadConfirmed = true; // Set the flag to true if confirmed
                    // Submit the form
                    $('[name="confirmUpload"]').val('confirm');
                    $('[name="confirmUpload"]').click();
                } else {
                    // Reset the file input if not confirmed
                    $('#file-upload').val('');
                }
            }
        }
        
         $('#file-upload').change(function() {
             fileUploadConfirmed = false
            confirmFileUpload(); // Call the confirmation function
            
        });
        
        
        }); 
    </script> 
    <script>
      function goBack() {
        window.location.href = 'Grades.php';
      }
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
    .note-container { 
                background-color: #f0f0f0; 
                padding: 10px; 
                margin-bottom: 15px; 
                border-radius: 5px; 
            } 
     
            .note-text { 
                font-style: italic; 
                color: #555; 
                font-size: 14px; 
            } 
            
            .backbutton {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
      }
    
      .backbutton:hover {
        background-color: #0056b3;
        border-color: #0056b3;
      }
    
      /* Optional: Adjust button padding and margin */
      .backbutton
      {
        padding: 8px 16px;
        margin-top: 10px;
      }
          .upload-grades-container {
        margin-top: 10px; /* Add top margin to separate from the main title */
    }

    @media (max-width: 767px) {
        .title-div {
            flex-direction: column;
            align-items: flex-start;
        }

        .backbutton {
            margin-bottom: 10px; /* Add spacing between buttons in mobile view */
             width: 100%; /* Full width button in mobile view */
            display: block; /* Convert button to block element in mobile view */
        }

        .upload-grades-container {
            width: 100%; /* Full width in mobile view */
            margin-top: 10px; /* Add top margin to separate from the main title */
        }

        .btn-success {
            width: 100%; /* Full width button in mobile view */
            display: block; /* Convert button to block element in mobile view */
        }
    }

     
    </style> 
