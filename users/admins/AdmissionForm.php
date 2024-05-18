<?php
session_start();
error_reporting(0);

//navigation styles
$astyle = "active";

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
      <!-- jquery -->
      <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
      <!-- to PDF js -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
      <!-- modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
  </head>
  <body>
    <?php
    //include navigationbar
    include('NavigationBar.php'); 

    //get students data
    $app_id = $_GET['id'];
    $query = mysqli_query($con,"SELECT a.*, c.acronym, u.email FROM applications a LEFT OUTER JOIN course_strand c ON a.course = c.id LEFT OUTER JOIN user_accounts u ON a.user_id=u.id WHERE a.app_id = $app_id");
    $ret=mysqli_fetch_array($query);
    $user_email = $ret['email'];
    $number = $ret['phone'];

    $src = 'data:image;base64,' . $ret['signature'];

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


    function sendEmail($subject,$message,$user_email){
      //php mailer
      include('../../confirmation_email.php');
      $recipient = $user_email;
      send_mail($recipient,$subject,$message);
    }
    
    function sendSMS($number, $message) {
    // Set your Semaphore API key
    $apiKey = 'insert_semaphore_api_key_here';

    // Create the cURL request
    $ch = curl_init();

    // Set the request URL
    $url = 'https://semaphore.co/api/v4/messages';

    // Set the POST data
    $postData = array(
        'apikey' => $apiKey,
        'number' => $number,
        'message' => $message
    );

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo '<script>alert("Error: ' . curl_error($ch) . '");</script>';
    } else {
        $result = json_decode($response, true);
        if (array_key_exists('number', $result)) {
          echo "<script>alert('Number invalid. Message not sent');</script>";
        } else {
          echo "<script>alert('Message sent successfully to number.'');</script>";
        }
    }
    // Close cURL resource
    curl_close($ch);
}

    if (isset($_POST['approve'])) {
      $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
      $sql = "UPDATE applications SET app_status=2, app_remarks_date=current_timestamp(), app_process_by=?, expiry=? WHERE app_id=?";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "ssi",  $username, $expiry, $app_id);
      if (mysqli_stmt_execute($stmt)) {
        //function to send email
            $subject = "Regarding your Admission to ACTS Computer College.";
            $message = "Congratulations! Your admission form has been approved. Please settle your payment within 24 hours to be successfully admitted, or you will have to resend your application. Log in to your ACTS Web Portal account for more information. Thank you.";
            $sms = "Congratulations! Your admission form has been approved. Please settle your payment within 24 hours to be successfully admitted. Thank you";
            sendEmail($subject,$message,$user_email);
            sendSMS($number, $sms);
        echo "<script type='text/javascript'>document.location ='AdmissionForm.php?id=$app_id';</script>";
      } else {
        mysqli_rollback($con);
        echo "Error: " . mysqli_error($con);
      }
    }

    //reject the application
    if (isset($_POST['reject'])) {
      $remarks = $_POST['remarks'];
      $sql = "UPDATE applications SET app_status=3, app_remarks =?, app_remarks_date=current_timestamp(), app_process_by=? WHERE user_id=?";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "ssi", $remarks, $username, $ret['user_id']);
      if (mysqli_stmt_execute($stmt)) {
        //function to send email
            $subject = "Regarding your Admission to ACTS Computer College.";
            $message = "We regret to inform you that your application for admission to ACTS Computer College has been rejected. Log in to your ACTS Web Portal account for more information.";
            sendEmail($subject,$message,$user_email);
         echo "<script type='text/javascript'>document.location ='AdmissionForm.php?id=$app_id';</script>";
      } else {
          // Display an error message if the faculty ID update fails
          echo $error_alert;
      }
      mysqli_stmt_close($stmt);
    }
    ?>

    <main>
      <div class="flex gap">
        <div>
          <p><small  class="darkred">STATUS: <?php echo $admin_status ?></small></p>
          <?php 
            if ($admin_status == "Rejected") {
              // if rejected show remarks ?>
              <p><small class="darkred">REMARKS: <?php echo $ret['app_remarks']; ?></small></p> <?php 
            }
            if ($admin_status != "Pending") { ?>
              <p><small class="darkred">PROCESS DATE: <?php echo date("F d, Y h:ia", strtotime($ret['app_remarks_date'])); ?></small></p> <?php 
            } ?>
        </div>
        <div class="flex">
          <button class="link" style="margin-right: 10px;" id="save" onclick="save()"><i class="las la-download"></i>Save as PDF</button>
          <form method="post" class="flex">
            <div>
              <a class="btn btn-primary" href="javascript:void(0)" onclick="window.open('get_image_db.php?id=<?php echo $ret['app_id']; ?>')"><i class="las la-search"></i>Grades</a>
            </div>
            <?php 
            if ($admin_status == "Pending") { 
              // if pending show action buttons acpprove or reject ?>
              <button class="btn btn-success" type="button" id="approve" name="approve" data-bs-toggle="modal" data-bs-target="#approve_modal"><i class="las la-check"></i>Approve</button>
              <button class="btn btn-danger" type="button" id="reject" name="reject" data-bs-toggle="modal" data-bs-target="#reject_modal"><i class="las la-times"></i>Reject</button> <?php 
            } ?>
          </form>
        </div>
      </div>     
      <form class="container" method="post" id="view-ad-form">
        <div id="viewAd"> 
          <div class="center calibri">
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
              <img id="img" src="<?php echo $src ?>" alt="Signature" style="width: auto; height: 100px; object-fit: contain">
              <p><strong><?php echo strtoupper($ret['fname'])." ".strtoupper(substr($ret['mname'],0,1)).". ".strtoupper($ret['lname']); ?></strong></p>
            </div>
          </div>
        </div>
      </form>
      <div class="modal fade" id="approve_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title for_reject" id="myModalLabel">Are you sure to Approve Admission?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form" method="post">
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
              <h5 class="modal-title for_reject" id="myModalLabel">Reject Admission</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form" method="post">
              <div class="modal-body">
                <div class="mb-3">
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
        
        link.addEventListener('click', function () {
            isDownloading = true;
        });
        
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
  }
  .details{
    margin-bottom: 20px;
  }
  .container{
     padding: 15px;
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
  .center{
    text-align: center;
  }
  .declaration{
    background: #f8d7da;
    color: #721c24;
  }    
  .link{
    background: none;
    border: none;
    color: var(--main-color);
    text-decoration: underline !important;
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
  }
</style>