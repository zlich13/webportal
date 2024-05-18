
<?php
session_start();
error_reporting(0);

//navigation styles
$vastyle = "active";

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
  	<title>ACTS Web Portal | Student Account Card</title>
  	<!-- bootstrap -->
  	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  	<!-- styles -->
    <link rel="stylesheet" href="../../css/navigation.css"/>
    <link rel="stylesheet" href="../../css/style.css"/>
    <!-- icons -->
  	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!--window icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
    <!-- to PDF js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</head>
<body>

<?php
//include navigationbar
include('NavigationBar.php'); 
$student_num = $ret['student_num'];
$sy_id = $sy_ret['sy_id'];
$sem_id = $sy_ret['sem_id'];
$s_year = $ret['student_year'];
$sem = $sy_ret['semester'];

function showEnrolledSubjects($student_num,$sem_id, $sy_id) {
    //show data on datatable based on mode and status filter
    global $table_contents, $con;
    $table_contents = "";
    $sql = "SELECT ss.subject_id, s.* FROM student_subjects ss LEFT OUTER JOIN subjects s ON ss.subject_id = s.subject_id WHERE ss.student_num = $student_num AND ss.sem_id = $sem_id AND ss.school_year_id = $sy_id";
    // execute SQL query and generate table contents
    $result = $con->query($sql); // execute SQL query and store result in $result variable
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $table_contents .= "<tr>
          <td>" . $row['subject_code'] . "</td>
          <td>" . $row["subject_description"] . "</td>
          <td>" . $row["units"] . "</td>
          <td>" . $row["has_lab"] . "</td></tr>";
      }
    $total = $con->query("SELECT SUM(s.units) AS total_units, SUM(s.has_lab) AS total_lab FROM student_subjects ss LEFT OUTER JOIN subjects s ON ss.subject_id = s.subject_id WHERE ss.student_num = $student_num AND ss.sem_id = $sem_id AND ss.school_year_id = $sy_id")->fetch_array();
    $table_contents .= "<tr class='table-success' style='font-weight: bold;'>
        <td> Total </td>
        <td> </td>
        <td>" . $total['total_units']. "</td>
        <td>" . $total['total_lab']. "</td>
        <tr>";
    } else {
      $table_contents .= "<tr><td class='center' colspan='3'>-- TO BE EVALUATED --</td></tr>";
    }
}

function showSubjects($s_year, $sem) {
    //show data on datatable based on mode and status filter
    global $table_contents, $con;
    $table_contents = "";

    if($sem == 1){
      $sql = "SELECT * FROM subjects WHERE ((sub_grade_year = $s_year + 1 AND $s_year IN (1, 2, 3, 11)) -- select subjects for next year for years 1, 2, 3, and 11
      OR (sub_grade_year = 1 AND $s_year = 12)) -- select subjects for year 1 for year 12
      AND semester = $sem";
    } else {
      $sql = "SELECT * FROM subjects WHERE sub_grade_year = $s_year AND semester = '$sem'";
    }
    // execute SQL query and generate table contents
    $result = $con->query($sql); // execute SQL query and store result in $result variable
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $table_contents .= "<tr>
          <td>" . $row['subject_code'] . "</td>
          <td>" . $row["subject_description"] . "</td>
          <td>" . $row["units"] . "</td>
          <td>" . $row["has_lab"] . "</td></tr>";
      }
    $total = $con->query("SELECT SUM(units) AS total_units, SUM(has_lab) AS total_lab FROM subjects WHERE sub_grade_year = '$s_year' AND semester  = '$sem'")->fetch_array();
    $table_contents .= "<tr class='table-success' style='font-weight: bold;'>
        <td> Total </td>
        <td> </td>
        <td>" . $total['total_units']. "</td>
        <td>" . $total['total_lab']. "</td>
        <tr>";
    } else {
      $table_contents .= "<tr><td class='center' colspan='3'>-- TO BE EVALUATED --</td></tr>";
    }
}

  if ($ret['enrolled_status'] == 1) {
    showEnrolledSubjects($student_num, $sem_id, $sy_id);
    echo "<style>
            .enrolled{
              display: block;
            }
            .not_enrolled{
              display: none;
            }
          </style>";
  } else {
    showSubjects($s_year, $sem);
    echo "<style>
            .enrolled{
              display: none;
            }
            .not_enrolled{
              display: block;
            }
          </style>";
  }
?>
	<main>
    <div class="enrolled" style="text-align: right;"><button class="link" id="save" onclick="save()"><i class="las la-download"></i>Save as PDF</button></div>
    <div class="not_enrolled" style="text-align: right;"><small class="darkred">Status: NOT ENROLLED</small></div>
    <form class="container" method="post" id="view-ad-form">
      <div id="viewAd" style="padding: 10px;"> 
        <div class="center times">
            <p><?php echo $info_ret['sc_name'] ?></p>
            <p><?php echo $info_ret['sc_add'] ?></p>
            <p>Tel.No. <?php echo $info_ret['sc_num'] ?></p>
        </div>
        <br>
        <div class="calibri">
                <strong class="enrolled"><p class="center">E-ACCOUNT CARD</p></strong>
                <strong class="not_enrolled" style="margin-bottom: 10px">
                  <p class="center">SUBJECTS FOR S.Y. <?php echo $sy_ret['year_start'].'-'.$sy_ret['year_end'].' ('. ($sy_ret['semester'] == 1 ? '1ST' : ($sy_ret['semester'] == 2 ? '2ND' : '')) . ' SEMESTER)'  ?></p>
                </strong>
                <div style="text-align: right;" class="not_enrolled"><button onclick="location.href='EnrolledTransaction.php';" id="enroll_btn" name="enroll_btn" type="button" class="btn btn-primary">Enroll Now</button></div>
                <div class="enrolled">
                <div class="info flex gap" style="margin-bottom: 10px;">  
                  <div class="flex gap">
                    <div class="left" style="margin-right: 10px;">
                      <p>Student No.</p>
                      <p>Name:</p>
                      <p>Course:</p>
                      <p>Grade/Year:</p>
                    </div>
                    <div class="right">
                      <p><small>Student No.</small>    <?php echo $student_num ?></p>
                      <p><small>Name:</small> <strong style="text-transform: uppercase;"><?php echo $ret['lname'].", ". $ret['fname'] ." ". $ret['mname'] ; ?></strong></p>
                      <p><small>Course:</small> <?php echo $ret['name'] ?></p>
                      <p><small>Grade/Year:</small> <?php echo $ret['student_year'] == 1 ? 'First Year' : ($ret['student_year'] == 2 ? 'Second Year' : ($ret['student_year'] == 3 ? 'Third Year' : ($ret['student_year'] == 4 ? 'Fourth Year' : ($ret['student_year'] == 11 ? 'Grade 11' : ($ret['student_year'] == 12 ? 'Grade 12' : ''))))); ?> - <?php echo $ret['sec_name'] ?></p>
                    </div>
                  </div>
                  <div class="flex gap">
                    <div class="left" style="margin-right: 10px;">
                      <p>Date of Registration:</p>
                      <p>School Year:</p>
                      <p>Semester:</p>
                    </div>
                    <div class="right">
                      <p><small>Date of Registration:</small> <?php echo date('F d, Y', strtotime($ret['enrolled_date'])); ?></p>
                      <p><small>School Year:</small> <?php echo $sy_ret['year_start']."-".$sy_ret['year_end'] ?></p>
                      <p><small>Semester:</small> <?php echo $sy_ret['semester'] == 1 ? 'First Semester' : ($sy_ret['semester'] == 2 ? 'Second Semester' : ''); ?></p>
                    </div>
                  </div>
                </div>
                </div>
                <div>
                  <table  class="table my-table">
                    <thead class="table-success">
                      <tr>
                        <th>COURSE CODE</th>
                        <th>DESCRIPTION</th>
                        <th>UNITS</th>
                        <th>LAB</th>
                      </tr>    
                    </thead>
                    <tbody>
                      <?php echo "$table_contents"; ?>
                    </tbody>
                  </table>
                </div>
              </div>
      </div>
    </form>
  </main>
  <script type="text/javascript">
    function save() {
        var element = document.getElementById('viewAd');
        var tables = element.querySelectorAll('.my-table'); // Select all tables with class 'my-table'
        for (var i = 0; i < tables.length; i++) {
          var table = tables[i];
          table.style.fontSize = '4px'; // Change the font size
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
</body>
</html>
<style type="text/css">
  .right small{
    display: none;
  }
  .fees td, .fees th{
    font-size: 12px;
  }
  .link{
    background: none;
    border: none;
    color: var(--main-color);
    text-decoration: underline !important;
  }
  .enrolled{
    padding: 0 10px;
  }
  table td{
    width: auto;
  }
  table{
    border: 1px solid black;
  }
  .darkred{
    color: #8b0000;
  }
  .flex{
    display: flex;
  }
  .gap{
    justify-content: space-between;
  }
  .calibri *{
    font-family:Calibri;
    font-size: 14px;
  }
  .center{
    text-align: center;
     padding: 0 10px;
  }
  .times p{
    font-family:Calibri;
    font-size: 14px;
  }
  @media screen and (max-width: 770px) {
     .right small, .info{
      display: block !important;
    }
    .right p{
      display: flex;
      flex-direction: row;
    }
    .right p small{
      margin-right: 10px;
    }
    .left{
      display: none;
    }
  }

  @media screen and (max-width: 500px) {
    main, .container{
      padding-left: 0;
      padding-right: 0;
    }
    *{
      font-size: 12px;
    }
  }
</style>