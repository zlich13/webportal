<?php 
session_start();
error_reporting(0);

$label = "Strand/Courses";
$clstyle = "active";

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
	<title>ACTS Web Portal | Class List</title>
	<!-- bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<!-- styles -->
  	<link rel="stylesheet" href="../../css/navigation.css"/>
  	<link rel="stylesheet" href="../../css/style.css"/>
  	<link rel="stylesheet" href="../../css/DataTables.css"/>
  <!-- icons -->
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
	<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <!--window icon-->
   <link rel="shortcut icon" href="../../images/actsicon.png"/>
    <!-- to PDF js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body>

<?php
include('NavigationBar.php');
$semester = $sy_ret['semester'] == 1 ? '1st' : '2nd';

 $sched_id = $_GET['id'];

  $sql = "SELECT cs.subject, cs.is_repeating, cs.sched_date, cs.repeating_data, cs.time_from, cs.time_to , r.room_name, sub.subject_code, sub.units, UPPER(CONCAT(fac.prefix, ' ', fac.fname, ' ', fac.mname, '. ', fac.lname)) AS faculty_name FROM class_schedule cs JOIN classrooms r ON cs.room_id=r.room_id JOIN subjects sub ON cs.subject = sub.subject_description JOIN faculty fac ON cs.faculty_id = fac.id WHERE cs.sched_id = ?";
  $stmt = $con->prepare($sql);
  $stmt->bind_param("i", $sched_id);
  $stmt->execute();
  $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $subject = $row['subject'];
      $is_repeating = $row['is_repeating'];
      $sched_date = $row['sched_date'];
      $repeating_data = $row['repeating_data'];
      $time_from = date("g:i", strtotime($row['time_from']));
      $time_to = date("g:i A", strtotime($row['time_to']));
      $room_name = $row['room_name'];
      $subject_code = $row['subject_code'];
      $units = $row['units'];
      $faculty_name = strtoupper($row['faculty_name']);

      if ($is_repeating == 1) {
        $repeating_data = json_decode($repeating_data, true);
        $dow = intval($repeating_data['dow']);
        $day_map = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat'
        ];
        $days_string = isset($day_map[$dow]) ? $day_map[$dow] : '';
      } else {
          $days_string = $sched_date;
      }
    }
	?>

<main>
    <div class="title-div flex gap justify-content-between align-items-center">
        <!-- Back button -->
        <button onclick="goBack()" class="btn btn-primary backbutton">Back to Faculties</button>
    <div style="text-align: right;"><button class="link" id="save" onclick="save()"><i class="las la-download"></i>Save as PDF</button></div>
    </div>
  <form class="container" method="post" id="view-ad-form">
        <div id="viewAd" style="padding: 10px;">
            <div class="center calibri">
               <div class="center calibri">
              <p><?php echo $info_ret['sc_name'] ?></p>
              <p><?php echo $info_ret['sc_add'] ?></p>
              <p>Tel.No. <?php echo $info_ret['sc_num'] ?></p>
        </div>
            </div>
             <br>
            <div class="calibri">
              <strong><p class="center">CLASS LIST</p></strong>
                <div class="flex gap">
                    <div>
                        <p>Course Code: <?php echo $subject_code ?></p> 
                        <p>Units: <?php echo $units ?></p> 
                        <p>Room: <?php echo $room_name ?></p> 
                    </div>
                    <div>
                        <p>Course Title: <?php echo $subject ?></p> 
                        <p>Time/Day(s): <?php echo $time_from."-".$time_to."/".$days_string ?></p> 
                        <p>Semester/SY: <?php echo $semester." / ".$sy_ret['year_start']."-".$sy_ret['year_end']; ?></p>   
                    </div>
                </div>
                <br>
            </div>
            <div id="tables_div">
                <table id="tbl"  class="table my-table">
                  <thead class="calibri table-success">
                    <tr>
                    <th>#</th>
                    <th>NAME</th>
                    <th>COURSE</th>
                    <th>YEAR & SECTION</th>
                    <th>REMARKS</th>
                    </tr>
                  </thead>
                  <tbody> <?php 
                  

                  $sql = "SELECT 
    s.student_num, 
    CONCAT(app.lname, ', ', app.fname, ' ', LEFT(app.mname, 1)) AS student_name, 
    c.acronym, 
    CONCAT(s.student_year, '-', sec.name) AS year_section, 
    ssub.subject_id, 
    g.remarks
FROM 
    student_list s 
    JOIN applications app ON s.user_id = app.user_id 
    JOIN course_strand c ON s.student_course = c.id 
    JOIN student_section ss ON s.student_num = ss.student_num 
    JOIN sections sec ON ss.section_id = sec.section_id 
    JOIN class_schedule_sections css ON ss.section_id = css.section_id 
    JOIN student_subjects ssub ON s.student_num = ssub.student_num 
    LEFT JOIN (
        SELECT 
            student_num, 
            MAX(id) AS max_grade_id 
        FROM 
            grades 
        GROUP BY 
            student_num
    ) max_grades ON s.student_num = max_grades.student_num 
    LEFT JOIN grades g ON s.student_num = g.student_num AND g.id = max_grades.max_grade_id
    JOIN subjects sub ON ssub.subject_id = sub.subject_id 
WHERE 
    css.class_id = ? 
    AND sub.subject_description = ? 
ORDER BY 
    year_section, student_name ASC";
    
                  $stmt = $con->prepare($sql);
                  $stmt->bind_param("is", $sched_id, $subject);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if ($result->num_rows > 0) {
                  $current_value = null;
                  $counter = 1;
                    while ($row = $result->fetch_assoc()) : 
                        $separator = $row['acronym'].'-'.$row['year_section']; // Column name to use as a separator
                      if ($separator !== $current_value) :  $current_value = $separator; ?>
                        <tr>
                          <td colspan="6" class="table-secondary" style="text-align: right;"><strong><?php ?></strong></td>
                        </tr>  <?php 
                      endif; ?>
                        <tr>
                          <td><?php echo $counter++; ?></td>
                          <td><?php echo $row['student_name']; ?></td>
                          <td><?php echo $row['acronym']; ?></td>
                          <td><?php echo $row['year_section']; ?></td>
                          <td><?php echo $row['remarks']; ?></td>
                        </tr> <?php 
                    endwhile; 
                  }?>
                  </tbody>
                </table>
            </div>
            <br>
            <div class="flex gap">
              <div></div>
              <div class="calibri" style="text-align: center; margin-top: 3rem;">
                <div class="signature-container">
                <div class="signature-line"></div>
                </div>   
                  <p><?php echo $faculty_name ?></p>  
                  <p>Instructor's Signature</p>  
              </div>
            </div>
        </div>
</main>
<script type="text/javascript">
 function save() {
        var element = document.getElementById('viewAd');
        var tables = element.querySelectorAll('.my-table'); // Select all tables with class 'my-table'
        for (var i = 0; i < tables.length; i++) {
          var table = tables[i];
          table.style.fontSize = '14px'; // Change the font size
        }

        // Set the paper size to legal format
        var opt = {
          margin: [0.5,0.5],
          filename: 'file.pdf',
          image: { type: 'jpeg', quality: 1},
          html2canvas: { scale: 2 },
          jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
        // Get the blob of the generated PDF
        var blob = pdf.output('blob');

        // Create a URL with the blob
        var url = URL.createObjectURL(blob);

        // Open the URL in a new window
        var win = window.open(url, '_blank');
        
        // Create a link to download the file
        var link = document.createElement('a');
        link.href = url;
        link.download = opt.filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Reload the current page
        location.reload();
      });
}
</script>
<script>
      function goBack() {
        window.location.href = 'ClassList.php';
      }
    </script>
</body>
</html>
<style type="text/css">
  .center{
    text-align: center;
  }
	.filter-div{
		width: 100%;
	}
	.calibri p, table, th, td{
    font-family:Calibri;
    font-size: 14px;
  }
	.gap{
    justify-content: space-between;
  }
  .flex{
  	display: flex;
  }
  .link{
    background: none;
    border: none;
    color: var(--main-color);
    text-decoration: underline !important;
  }
  .table{
    border-spacing: 0;
  }
  .table td{
    padding: 0 !important;
  }
  .signature-container {
    display: flex;
    align-items: center;
  }
  
  .signature-line {
    flex-grow: 1;
    height: 1px;
    background-color: #000; /* Adjust the color as per your preference */
    margin-right: 10px; /* Adjust the spacing between the line and the text */
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

     
    }

</style>