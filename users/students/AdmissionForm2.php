<?php
session_start();
error_reporting(0);

//navigation styles
$afstyle = "active";

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
  	<title>ACTS Web Portal | Student Admission Form</title>
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

$src = 'data:image;base64,' . $ret['signature'];

//redirect if no application submitted
if(is_null($ret['app_id'])){
  echo "<script> window.location.href = 'AdmissionForm.php';</script>";
}

//convert status to text
$admin_status = ($ret['app_status'] == 1) ? "Pending" : 
                (($ret['app_status'] == 2) ? "Approved" : 
                (($ret['app_status'] == 3) ? "Rejected" : null));

// convert gender to text
$gen = ($ret['gen'] == 1) ? "Male" : 
            (($ret['gen'] == 2) ? "Female" : null);

// convert category to text
$category = ($ret['category'] == 1) ? "New Student" : 
            (($ret['category'] == 2) ? "Transferee" : null);


?>
	<main>
    <div class="flex gap">
      <div>
        <p><small class="darkred">STATUS: <?php echo $admin_status ?></small></p>
        <?php 
        if ($admin_status != "Pending") { 
          // if not pending, show the date processed ?>
          <p><small class="darkred">PROCESS DATE: <?php echo date("F d, Y h:ia", strtotime($ret['app_remarks_date'])); ?></small></p> <?php 
        } 
        if ($admin_status == "Rejected") {
          // if rejected show remarks and resend option ?>
          <p><small class="darkred">REMARKS: <?php echo $ret['app_remarks'] ?></small></p>
          <p><a href="AdmissionForm.php" class="link"><small><i class="las la-redo-alt"></i> Resend an Application</small></a></p> <?php 
        } 
       ?>
      </div>
      <div><button class="link" id="save" onclick="save()"><i class="las la-download"></i>Save as PDF</button></div>
    </div>
        
    <form class="container" method="post" id="view-ad-form">
      <div id="viewAd"> 
        <div class="center times">
            <p><?php echo $info_ret['sc_name'] ?></p>
            <p><?php echo $info_ret['sc_add'] ?></p>
            <p>Tel.No. <?php echo $info_ret['sc_num'] ?></p>
        </div>
        <br>
        <div class="calibri">
          <div>
            <strong><p class="center">ADMISSION FORM</p></strong>
          </div>

          <div class = "details">
            <span class="details_title">PERSONAL DETAILS</span>
            <table class="my-table">
              <tr>
                <th>NAME: </th>
                <td><?php echo $ret['lname'].", ". $ret['fname'] ." ". $ret['mname'] ; ?></td>
              </tr>
              <tr>
                <th>CATEGORY: </th>
                <td><?php echo $category ?></td>
              </tr>
              <tr>
                <th>YEAR & COURSE: </th>
                <td><?php echo $ret['year']; ?>-<?php echo $ret['acronym']; ?></td>
              </tr>
              <tr>
                <th>BIRTHDATE: </th>
                <td><?php echo date('F d, Y', strtotime($ret['birth'])); ?></td>
              </tr>
              <tr>
                <th>GENDER: </th>
                <td><?php echo $gen ?></td>
              </tr>
              <tr>
                <th>STATUS: </th>
                <td><?php echo $ret['s_status']; ?></td>
              </tr>
              <tr>
                <th>EMAIL ADDRESS: </th>
                <td><?php echo $ret['email']; ?></td>
              </tr>
              <tr>
                <th>PHONE NUMBER: </th>
                <td><?php echo $ret['phone']; ?></td>
              </tr>
              <tr>
                <th>NATIONALITY: </th>
                <td><?php echo $ret['nationality']; ?></td>
              </tr>
              <tr>
                <th>COMPLETE ADRESS: </th>
                <td><?php echo $ret['address']; ?></td>
              </tr>
            </table>
          </div>

          <div class = "details">
            <span class="details_title">FAMILY DETAILS</span>
            <table class="my-table">
              <tr>
                <th>MOTHER'S INFORMATION: </th>
                <td><?php echo $ret['mother']; ?></td>
              </tr>
              <tr>
                <th></th>
                <td><?php echo $ret['mother_phone']; ?></td>
              </tr>
              <tr>
                <th></th>
                <td><?php echo $ret['mother_occu']; ?></td>
              </tr>
              <tr >
                <th>FATHER'S INFORMATION: </th>
                <td><?php echo $ret['father']; ?></td>
              </tr>
              <tr>
                <th></th>
                <td><?php echo $ret['father_phone']; ?></td>
              </tr>
              <tr>
                <th></th>
                <td><?php echo $ret['father_occu']; ?></td>
              </tr>
              <?php 
                $rel = $ret['relation'];
                if ($rel == "Mother" || $rel == "Father") { 
                  //if guardian is mother/father dont show repeated data ?>
                  <tr>
                    <th>GUARDIAN: </th>
                    <td><?php echo $ret['relation']; ?></td>
                  </tr> <?php 
                } else { 
                  //if not show guardian's data ?>
                  <tr>
                    <th>GUARDIAN: </th>
                    <td><?php echo $ret['guardian']; ?></td>
                  </tr>
                  <tr>
                    <th></th>
                    <td><?php echo $ret['guardian_phone']; ?></td>
                  </tr>
                  <tr >
                    <th></th>
                    <td><?php echo $ret['relation']; ?></td>
                  </tr> <?php 
                }
              ?>
            </table>
          </div>

          <div class = "details">
            <span class="details_title">EDUCATIONAL BACKGROUND</span>
                <table class="my-table">
                  <tr>
                    <th>ELEMENTARY SCHOOL: </th>
                    <td><?php echo $ret['elem']; ?></td>
                  </tr>
                  <tr >
                    <th></th>
                    <td><?php echo $ret['elem_year']; ?></td>
                  </tr>
                  <tr >
                    <th>JUNIOR HIGH SCHOOL: </th>
                    <td><?php echo $ret['junior']; ?></td>
                  </tr>
                  <tr>
                    <th></th>
                    <td><?php echo $ret['junior_year']; ?></td>
                  </tr>
                  <tr >
                    <th>SENIOR HIGH SCHOOL: </th>
                    <td><?php echo $ret['senior']; ?></td>
                  </tr>
                  <tr>
                    <th></th>
                    <td><?php echo $ret['strand']; ?></td>
                  </tr>
                  <tr>
                    <th></th>
                    <td><?php echo $ret['senior_year']; ?></td>
                  </tr>
                  <tr>
                    <th>PREVIOUS COLLEGE SCHOOL/UNIVERSITY: </th>
                    <td><?php echo $ret['college']; ?></td>
                  </tr>
                  <tr >
                    <th></th>
                    <td><?php echo $ret['old_course']; ?></td>
                  </tr>
                  <tr >
                    <th></th>
                    <td><?php echo $ret['college_year']; ?></td>
                  </tr>
                </table>
              </div>
              <span class="details_title declaration">DECLARATION AND AGREEMENT</span>
              <div class="d_div">
                <p class="center"><i>I hereby state that the facts mentioned above are true to the best of my knowledge and belief. I also hereby promise to pass the original copies of my PSA Birth Certificate, Good Moral Character Certificate, <?php echo $ret['agreement'] ?> , and recent 2x2 pictures on or before <strong class="darkred"><?php echo date("F d, Y", strtotime($ret['prom_date']));?></strong>.</i></p>
                <canvas id="signature-pad" hidden></canvas>
                <div class="sign-div">
                <img id="img" src="<?php echo $src ?>" alt="Signature" style="width: auto; height: 100px; object-fit: contain">
                <p><strong><?php echo strtoupper($ret['fname'])." ".strtoupper(substr($ret['mname'],0,1)).". ".strtoupper($ret['lname']); ?></strong></p>
                </div>
              </div>
            </div>
          </div>
        </form>
  </main>
  <script type="text/javascript">
    var canvas = document.getElementById('signature-pad');
    var context = canvas.getContext('2d');
    var image = document.getElementById('img');

    // Set the canvas width and height
    var aspectRatio = image.width / image.height;
    canvas.width = aspectRatio * 100;
    canvas.height = 100;

    // Draw the image on the canvas
    context.drawImage(image, 0, 0, canvas.width, canvas.height);

    // Adjust the brightness and contrast
    context.filter = 'brightness(150%) contrast(150%)';

    // Get the image data from the canvas
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    
    function save() {
        var element = document.getElementById('viewAd');
        var tables = element.querySelectorAll('.my-table'); // Select all tables with class 'my-table'
        for (var i = 0; i < tables.length; i++) {
          var table = tables[i];
          table.style.fontSize = '14px'; // Change the font size

          // Select all tr elements inside the table and set their display to flex
          var rows = table.querySelectorAll('tr');
          for (var j = 0; j < rows.length; j++) {
            var row = rows[j];
            row.style.display = 'flex';
          }

          var cells = table.querySelectorAll('th, td');
          for (var j = 0; j < cells.length; j++) {
            var cell = cells[j];
            if (cell.tagName.toLowerCase() == 'th') { // Check if the element is a TH
              cell.style.width = '40%'; // Set the width to 40%
            }
            if (cell.tagName.toLowerCase() == 'td') { // Check if the element is a TH
              cell.style.width = '60%'; // Set the width to 60%
            }
          }
        }


        var img = document.getElementById('img');
        var canvas = document.getElementById('signature-pad');
        img.hidden = true;
        canvas.hidden = false;

        // Set the paper size to legal format
        var opt = {
          margin: 0.5,
          filename: 'file.pdf',
          image: { type: 'jpeg', quality: 1},
          html2canvas: { scale: 2 },
          jsPDF: { unit: 'in', format: 'legal', orientation: 'portrait' }
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
  .darkred{
    color: #8b0000;
  }
  .flex{
    display: flex;
    padding: 0 10px;
  }
  .gap{
    justify-content: space-between;
  }
  canvas, form img{
    border-bottom: 1px solid black;
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
    font-family: Calibri;
    font-size: 14px;
  }
  .right{
    text-align: right;
    margin-bottom: 10px;
  }
  .details{
    margin-bottom: 20px;
  }
  .container{
     padding: 15px;
  }
  .link{
    background: none;
    border: none;
    color: var(--main-color);
    text-decoration: underline !important;
  }
  .d_div p{
    padding: 0 10px;
  }
  .details_title{
    display: block;
    font-weight: bold;
    background: #d4edda;
    color:  #155724;
    padding: 5px;
    margin: 10px 0;
  }
  #main-title{
    text-align: center;
  }  
  table {
    width: 100%;
  }  
  th{
    width: 40%;
  }
  td{
    width: 60%;
  }
  th, td {
    box-sizing: border-box;
    padding: 0 10px;
  }
  .declaration{
    background: #f8d7da;
    color: #721c24;
  }    
  .sign-div{
    position: absolute;
    right: 0;
    padding: 10px 0;
  }
  @media screen and (max-width: 500px) {
    main, .container{
      padding-left: 0;
      padding-right: 0;
    }
    th, td {
      display: block !important;
      width: 100%;
    }
    .flex{
      font-size: 12px;
    }
    form img{
      height: 80px !important;
    }
  }
  .details table{
    margin: 0 10px;
  }
  @media screen and (max-width: 300px) {
    .flex{
      display: block;
    }
    form img{
      height: 60px !important;
    }
  }

</style>