<?php
session_start();
error_reporting(0);

//navigation styles
$dstyle = "active";

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
  <title>ACTS Web Portal | Dashboard</title>
  <!-- bootstrap css -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <!-- page css -->
  <link rel="stylesheet" href="../../css/navigation.css"/>
  <link rel="stylesheet" href="../../css/style.css"/>
  <!--icon-->
  <link rel="shortcut icon" href="../../images/actsicon.png"/>
  <!-- lineawesome icons -->
  <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
  <!-- modal -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>
      
<body>
  <?php
    //include navigationbar
    include('NavigationBar.php');

    // Fetch announcements from the database
$sqlAnnouncements = "SELECT * FROM announcements";
$resultAnnouncements = $con->query($sqlAnnouncements);

// Check if there are any announcements
if ($resultAnnouncements->num_rows > 0) {
    // Fetch all announcements into an associative array
    $announcements = $resultAnnouncements->fetch_all(MYSQLI_ASSOC);
} else {
    // No announcements found
    $announcements = array();
}

    $sql = "SELECT phone, is_p_verified, guardian_phone, is_gp_verified FROM applications WHERE user_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
      if ($row['is_p_verified'] == 0 || $row['is_gp_verified'] == 0) {
        $guardian_phone = $row['guardian_phone'];
        $phone = $row['phone'];
        echo "<style>
                #verifyNumberDiv{
                  display: block !important;
                }
            </style>";
        if ($row['is_p_verified'] == 0) {
          echo "<style>
                #verifyPhone{
                  display: block !important;
                }
            </style>";
          $status =  checkVerification($phone);
          if ($status == 1) {
            echo "<style>
                    #confirmPhone{
                      display: block !important;
                    }
                    #sendPhoneCode{
                      display: none;
                    }
                  </style>";
          }
        }

        if ($row['is_gp_verified'] == 0) {
          echo "<style>
                #verifyGPhone{
                  display: block !important;
                }
            </style>";
          $status =  checkVerification($guardian_phone);
          if ($status == 1) {
            echo "<style>
                    #confirmGPhone{
                      display: block !important;
                    }
                    #sendGPhoneCode{
                      display: none;
                    }
                  </style>";
          }
        }
      }
    }

    function checkVerification($number){
      global $con;
      $sql = "SELECT id FROM phone_verify WHERE phone = ? AND expires > ?";
      $stmt = $con->prepare($sql);
      $stmt->bind_param('si', $number, time());
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      if ($row) {
        return 1;
      } else {
        return 0;
      }
    }

    if (isset($_POST['verifyPBtn'])) {
      $hidden_num = $_POST['hidden_number'];
      $get_code = $_POST['number_code'];
      $sql = "SELECT id FROM phone_verify WHERE phone = ? AND code = ?";
      $stmt = $con->prepare($sql);
      $stmt->bind_param('si', $hidden_num, $get_code);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      if ($row) {
          $stmt = $con->prepare("UPDATE applications SET is_p_verified = 1 WHERE user_id = ?");
          $stmt->bind_param("i", $uid);
          if ($stmt->execute()) {
            echo "<script>alert('Verification Succesful!')</script>";
            echo "<script type='text/javascript'>document.location ='StudentDashboard.php';</script>";
          } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
          }
      } else {
         echo "<script>alert('Code incorrect!')</script>";
      }
    }

    if (isset($_POST['verifyGPBtn'])) {
      $hidden_num = $_POST['hidden_gnumber'];
      $get_code = $_POST['gnumber_code'];
      $sql = "SELECT id FROM phone_verify WHERE phone = ? AND code = ?";
      $stmt = $con->prepare($sql);
      $stmt->bind_param('si', $hidden_num, $get_code);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      if ($row) {
          $stmt = $con->prepare("UPDATE applications SET is_gp_verified = 1 WHERE user_id = ?");
          $stmt->bind_param("i", $uid);
          if ($stmt->execute()) {
            echo "<script>alert('Verification Succesful!')</script>";
            echo "<script type='text/javascript'>document.location ='StudentDashboard.php';</script>";
          } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
          }
      } else {
         echo "<script>alert('Code incorrect!')</script>";
      }
    }

  ?>
    
  <main>
    <div class="title_div">
      <h5 class="title" id="main-title">Dashboard:</h5>  
     <h5 class="title sy">S.Y. <?php echo isset($sy_ret['sy_id']) ? $sy_ret['year_start']."-".$sy_ret['year_end']." " : ''; ?> <small class="sy"><?php echo isset($sy_ret['semester']) ? ($sy_ret['semester'] == 1 ? '1st Semester' : ($sy_ret['semester'] == 2 ? '2nd Semester' : '')) : '';?> <?php echo isset($ret['enrolled_status']) ? ($ret['enrolled_status']!= null && $ret['enrolled_status'] != 0 ? '(Enrolled)' : '(Not enrolled)') : ''; ?></small></h5>
    </div>
    <?php 
      //check if user logged in is already have application
      if(is_null($ret['app_id'])){
        // if no application form
        $percent = 20; ?>
        <a href="AdmissionForm.php">
          <div class = "next">
            <div  class="card-single">
              <div>
                <span>Welcome to ACTS Web Portal! Fill out the admission form now!</span>
              </div>
              <div>
                <span class="las la-chevron-circle-right"></span>
              </div>
            </div>
          </div>
        </a> <?php 
      } else {
        // have application form
        if ($ret['app_status'] == 1) {
          // if application form pending
          $percent = 40; ?>
          <div>
            <div  class="card-single done">
              <div>
                <span>Admission form submitted! Please wait for admin confirmation. Thank you!</span>
              </div>
              <div>
                <span class="las la-check-circle"></span>
              </div>
            </div>
          </div> <?php 
        } else if ($ret['app_status'] == 3) {
          // if application form rejected
          $percent = 60; ?>
          <a href="AdmissionForm2.php">
            <div>
              <div  class="card-single reject">
                <div>
                  <span>Sorry, your application has been Rejected.</span>
                </div>
                <div>
                  <span class="las la-times-circle"></span>
                </div>
              </div>
            </div>
          </a> <?php 
        } else if ($ret['app_status'] == 2) {
          // if application form approved check if already sent payment
          if (is_null($ret['trans_id'])) {
            // if no payment
            $percent = 60; ?>
            <a href="Transaction.php">
              <div class = "next">
                <div  class="card-single">
                  <div>
                    <span>Your application has been Approved! Settle payment to be officially enrolled.</span>
                  </div>
                  <div>
                    <span class="las la-chevron-circle-right"></span>
                  </div>
                </div>
              </div>
            </a> <?php 
          } else {
            // if paid
            if ($ret['trans_status'] == 1) {
              // if proof of payment pending
              $percent = 80 ?>
              <div>
                <div  class="card-single done">
                  <div>
                    <span>Proof of Payment sent! Please wait for admin for your payment confirmation.</span>
                  </div>
                  <div>
                    <span class="las la-check-circle"></span>
                  </div>
                </div>
              </div> <?php 
            } else if ($ret['trans_status'] == 3) {
              // if proof of payment is rejected
              $percent = 90 ; ?>
              <a href="Transaction.php">
                <div>
                  <div class="card-single reject">
                      <div>
                        <span>Sorry, your Payment Transaction has been Rejected.</span>
                      </div>
                      <div>
                        <span class="las la-times-circle"></span>
                      </div>
                  </div>
                </div>
              </a> <?php 
            } else if ($ret['trans_status'] == 2) {
              // if proof of payment is approved check if officially enrolled
              if (is_null($ret['student_num'])) {
                // if not enrolled
                $percent = 100 ; ?>
                <a href="Enroll.php?app=<?php echo $ret['app_id'] ?>"> 
                  <div class="next">
                    <div  class="card-single">
                      <div>
                        <span>Congratulations! Your Payment Transaction has been Approved. Click here to be Officially Enrolled</span>
                      </div>
                      <div>
                        <span class="las la-chevron-circle-right"></span>
                      </div>
                    </div>
                  </div>
                </a> <?php 
              } else {
                // if enrolled
                echo '<style type="text/css">
                          .row{
                            display: none;
                          }
                      </style>'; ?>

<div class="announcement-section">
    <div class="announcement-header">
        <h1>Announcements:</h1>
        
    </div>

    <?php
    // Check if there are announcements
    if (count($announcements) > 0) {
      echo '<div class="announcement-content">';

      // Add the carousel structure
      echo '<div id="announcementCarousel" class="carousel slide" data-bs-ride="carousel">';
      echo '<div class="carousel-inner">';

      foreach ($announcements as $key => $announcement) {
          echo "<div class='carousel-item announcement-item" . ($key === 0 ? " active" : "") . "'>";
          echo "<h3 class='announcement-title'>" . $announcement['announcement_title'] . "</h3>";

          if ($announcement['announcement_type'] === 'image' && !empty($announcement['image_path'])) {
              echo "<p>" . $announcement['announcement_content'] . "</p>";

              echo "<div class='imgdiv'>";
              echo "<img src='" . $announcement['image_path'] . "' alt='Announcement Image' class='imagedisplay' >";
              echo"</div>";
          } else {
              echo "<p>" . $announcement['announcement_content'] . "</p>";
          }

          echo "</div>";
      }

        // Add the carousel controls and indicators
        echo '</div>';
        echo '<button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev">';
        echo '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
        echo '<span class="visually-hidden">Previous</span>';
        echo '</button>';
        echo '<button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next">';
        echo '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
        echo '<span class="visually-hidden">Next</span>';
        echo '</button>';
        echo '</div>';

        // Add the carousel indicators
        echo '<div class="announcement-indicators">';
        foreach ($announcements as $key => $announcement) {
            echo "<span data-bs-target='#announcementCarousel' data-bs-slide-to='$key' class='" . ($key === 0 ? "active" : "") . "'></span>";
        }
        echo '</div>';

        echo '</div>';
    } else {
        // Display a message if there are no announcements
        echo "<div class='empty-announcement'>No announcements available.</div>";
    }
    ?>
</div>
                     
                <div class="cards">
                    <div id="verifyNumberDiv" hidden>
                      <div class="card-single cs reject" data-bs-toggle="modal" data-bs-target="#verify_numbers">
                        <div>
                          <span>Action Required</span>
                        </div>
                        <div>
                          <span class="las la-exclamation-circle"></span>
                        </div>
                      </div>
                    </div>
                  <a href="ViewSchedules.php">
                    <div class="next">
                      <div class="card-single cs">
                        <div>
                          <span>Schedules</span>
                        </div>
                        <div>
                          <span class="las la-calendar"></span>
                        </div>
                      </div>
                    </div>
                  </a>
                  <a href="ViewAccountCard.php">
                    <div class="next">
                      <div class="card-single cs">
                        <div>
                          <span>Account Card</span>
                        </div>
                        <div>
                          <span class="las la-file-invoice"></span>
                        </div>
                      </div>
                    </div>
                  </a>
                  <a href="EnrolledTransaction.php">
                    <div class="next">
                      <div class="card-single cs">
                        <div>
                          <span>Transactions</span>
                        </div>
                        <div>
                          <span class="las la-credit-card"></span>
                        </div>
                      </div>
                    </div>
                  </a>
                  <a href="ViewGrades.php">
                    <div class="next">
                      <div class="card-single cs">
                        <div>
                          <span>Grades</span>
                        </div>
                        <div>
                          <span class="las la-pen-nib"></span>
                        </div>
                      </div>
                    </div>
                  </a>
                </div> <?php 
              } 
            }
          }
        }
      }
    ?>           
    <div class="row">
      <div class="progress">
        <div class="progress-bar" role="progressbar" style="width: <?php echo $percent;?>%;" aria-valuenow="<?php echo $percent ?>" aria-valuemin="0" aria-valuemax="100">
          <?php echo $percent ?>%
        </div>
      </div>
    </div>
    

    <!-- Modal for Verifying numbers -->
    <div class="modal" id="verify_numbers" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title for_add" id="myModalLabel">Verify Numbers</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div id="verifyPhone" hidden>
                <form method="post">
                  <div class="mb-1">
                    <label for="modal_number" class="form-label">Your Phone Number</label>
                    <div class="flex" style="width: 100%">
                      <input id="modal_number" name="modal_number" type="text" readonly class="form-control" value="<?php echo $phone ?>"  style="width:70%;">
                      <button type="button" id="sendPhoneCode" class="btn btn-success"  style="width: 30%">Send Code</button>
                    </div>
                  </div>
                </form>
                <form method="post" id="confirmPhone" hidden>
                  <div class="mb-3">
                    <div class="flex" style="width: 100%">
                      <input id="hidden_number" name="hidden_number" type="text" hidden readonly class="form-control" value="<?php echo $phone ?>"  style="width:70%;">
                      <input id="number_code" name="number_code" placeholder="Enter Verification Code" type="number" class="form-control" style="width:70%;" required>
                      <button button = "submit" id="verifyPBtn" name="verifyPBtn" class="btn btn-success" style="width: 30%">Verify</button>
                    </div>
                  </div>
                </form>
              </div>
              <div id="verifyGPhone" hidden>
                <form method="post">
                  <div class="mb-1">
                    <label for="modal-gnumber" class="form-label">Guardian's Phone Number</label>
                    <div class="flex" style="width: 100%">
                      <input id="modal_gnumber" name="modal_gnumber" type="text" readonly class="form-control" value="<?php echo $guardian_phone ?>" style="width:70%;">
                      <button type="button" name="sendGPhoneCode" id="sendGPhoneCode" class="btn btn-success" style="width: 30%">Send Code</button>
                    </div>
                  </div>
                </form>
                <form id="confirmGPhone" method="post" hidden>
                  <div class="mb-3">
                    <div class="flex" style="width: 100%">
                       <input id="hidden_gnumber" name="hidden_gnumber" type="text"  hidden readonly class="form-control" value="<?php echo $guardian_phone ?>" style="width:70%;">
                      <input  id="gnumber_code" name="gnumber_code" placeholder="Enter Verification Code" type="number" class="form-control" style="width:70%;" required>
                      <button  button="submit" id="verifyGPBtn" name="verifyGPBtn" class="btn btn-success" style="width: 30%">Verify</button>
                    </div>
                  </div>
                </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    $(document).ready(function() {
      $('#sendPhoneCode').click(function() {
        var number = $('#modal_number').val();
        $.ajax({
          url: 'verify_numbers.php',
          method: 'POST',
          data: {number: number},
          dataType: 'json',
          success: function(respo) {
            if (respo.status === 'success') {
              $('#confirmPhone').removeAttr('hidden');
              $('#sendPhoneCode').hide();
              console.log(respo.message);
            } else {
              alert(respo.message);
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr);
            console.error(status);
            console.error(error);
          }
        });
      });

      $('#sendGPhoneCode').click(function() {
        var number = $('#modal_gnumber').val();
        $.ajax({
          url: 'verify_numbers.php',
          method: 'POST',
          data: {number: number},
          dataType: 'json',
          success: function(respo) {
            if (respo.status === 'success') {
              $('#confirmGPhone').removeAttr('hidden');
              $('#sendGPhoneCode').hide();
              console.log(respo.message);
            } else {
              alert(respo.message);
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr);
            console.error(status);
            console.error(error);
          }
        });
      });
    });
  </script>
  <script>
    $(document).ready(function () {
        // Initialize the carousel
        $('#announcementCarousel').carousel();

        // Handle the slide event to update indicators
        $('#announcementCarousel').on('slid.bs.carousel', function () {
            var currentIndex = $('#announcementCarousel .carousel-inner .active').index() + 1;
            $('.announcement-indicators span').removeClass('active');
            $('.announcement-indicators span:nth-child(' + currentIndex + ')').addClass('active');
        });
    });
</script>
</body>
</html>
<style type="text/css">
  .flex{
    display: flex;
  }
  .reject{
    background-color: #f8d7da;
  }
  .progress{
    --bs-progress-bg: none;
    border-radius: none;
  }
  .progress-bar {
    background-color: var(--main-color);
  }

  .next .card-single{
    background: #cdfacf;
  }
  .done:hover{
  cursor: default;
  box-shadow: none;
  }
  .card-single{
    padding:  10px 10px;
    height: auto;
    margin-top: 1rem;
  }
  .cs{
    padding:  32px;
    margin: 0;
  }
  #stat_div{
    padding: 20px;
  }
  @media(max-width: 550px){
    *{
      font-size: 12px;
    }

  }

  .announcement-section {
    margin-top: 20px;
    width: 60%;
    margin: 0 auto;
    border: 1px solid #ccc;
    border-radius: 8px;
    overflow: hidden;
}

div.announcement-section .announcement-header {
    
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f0f0f0;
    padding: 0px;
    border-bottom: 1px solid #ccc;
    border-radius: 8px 8px 0 0;
	
}

.announcement-header h1 {
    margin: 10px;
    font-size: 1.3em;
    color: #333;
}

.announcement-content {
    padding: 15px;
    margin-top: 15px;
    max-height: 300px;
    overflow-y: auto;
}

.announcement {
    background-color: #f9f9f9;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 6px;
}

.empty-announcement {
    color: #888;
    text-align: center;
    padding:50px;
}

/* Style for the button inside the announcement section */
.announcement-section button {
    margin-left: auto;
    margin-top: 0px;
    border: none;
    color: #fff;
    background-color: #28a745;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.announcement-section button:hover {
    background-color: #218838;
}



.announcement-section .btn {
    margin: 3px;
}

.announcement-indicators {
    text-align: center;
    margin-top: 10px;
}

.announcement-indicators span {
    display: inline-block;
    width: 10px;
    height: 10px;
    background-color: #888;
    border-radius: 50%;
    margin: 0 5px;
    cursor: pointer;
}

.announcement-indicators span.active {
    background-color: #28a745;
}
.carousel-control-prev, .carousel-control-next {
    background-color: green !important;
	width:50px;
	opacity:30%;
}

span.carousel-control-next-icon, span.carousel-control-prev-icon  {
    background-color: green !important;
    color:green !important;
    border-color: green !important;
    fill: green !important;
    width: 50px; 
    height: 50px; 
	 margin-top: 20px;
}
.addsucc{
	color: #28a745;
	font-size: 15px;
	padding:0px;
	margin:0px;
}

.carousel-inner img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.announcement-content img {
    max-height: none; 
}

.announcement-item, .carousel-item {
    height: 200px; 
   overflow:auto;
   padding: 0 60px;
   
}


.carousel-control-prev,
.carousel-control-next {
    background-color: green !important;
    width: 50px;
    opacity: 30%;
}

span.carousel-control-next-icon,
span.carousel-control-prev-icon {
    background-color: green !important;
    color: green !important;
    border-color: green !important;
    fill: green !important;
    width: 50px;
    height: 50px;
    margin-top: 20px;
}
.imgdiv{
    width:100%;
    height:100%;
    display:flex;
    justify-content: center;
    align-items: center;
    flex-direction:column;
}
.announcement-item h3{
    margin:0;
}
.carousel-item img{
    width: auto;
    height:100%;
}
.carousel-item p{
  margin: 0;
}

@media(max-width: 768px) {
  .announcement-section {
    width: 100%;
    margin: 0 auto;
  }

  .announcement-content {
    padding: 10px;
  }

  .carousel-item,
  .announcement-item {
    height: auto;
    text-align:justify;
    padding: 0 45px;
  }
  .carousel-control-prev,
.carousel-control-next {
    width:12%;
}

  .carousel-item img {
    width: 100%;
    height: auto;
  }

  .announcement-item h3 {
    font-size: 1.2em;
  }
}

</style>