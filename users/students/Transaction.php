<?php 
  session_start();
  error_reporting(0);

  //navigation styles
  $tstyle = "active";

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ACTS Web Portal | Payment Transaction</title>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- styles -->
    <link rel="stylesheet" href="../../css/navigation.css"/>
    <link rel="stylesheet" href="../../css/style.css"/>
    <!-- icons -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!--window icon-->
     <link rel="shortcut icon" href="../../images/actsicon.png"/>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  </head>

  <body>
    <?php 
      //include navigationbar
      include('NavigationBar.php'); 

      if($ret['app_status']!=2){
        echo "<script> window.location.href = 'StudentDashboard.php';</script>";
      }
      
      if(!is_null($ret['trans_id'])){
        // if user already have transaction, set fields uneditable
        echo "<style>
          .form-group input, .form-group select, .form-group textarea {
            pointer-events: none;
          }
        </style>";
      }

      // add transaction
      if (isset($_POST['add'])) {
        $mode = $_POST['mode'];
        $ref = $_POST['ref'];
        $date = $_POST['date'];
        $sender = $_POST['sender'];
        $amount = $_POST['amount'];
        $purpose = "Enrollment";

        $tmpName = $_FILES['proof']['tmp_name'];
        $proof = base64_encode(file_get_contents(addslashes($tmpName)));

        // update data if previous transaction is rejected
        if ($ret['trans_status'] == 3) {
          $sql = "UPDATE transactions SET mode=?, ref_num=?, trans_date=?, sender=?, amount=?, proof=?, trans_status=1, trans_remarks='', trans_process_date = null, trans_process_by = null WHERE user_id = $uid";
          $stmt = mysqli_prepare($con, $sql);
          mysqli_stmt_bind_param($stmt, "ssssis", $mode, $ref, $date, ucwords($sender), $amount, $proof);
          if (mysqli_stmt_execute($stmt)) {
            echo "<script type='text/javascript'> document.location ='StudentDashboard.php'; </script>";
          } else {
            echo "<script>alert('Error! try again later.');</script>";
          }
          //close the statement
          mysqli_stmt_close($stmt);
        } else {
          //insert new transaction 
          $stmt = $con->prepare("INSERT INTO transactions (user_id, mode, ref_num, trans_date, sender, amount, proof, purpose) VALUES ($uid,?,?,?,?,?,?,?)");
          $stmt->bind_param("ssssiss", $mode, $ref, $date, ucwords($sender), $amount, $proof, $purpose);
          // Check for errors
          if($stmt->errno) {
            echo "Error: " . $stmt->error;
          }
          // Execute the statement
          if ($stmt->execute()) {
            echo "<script type='text/javascript'>document.location ='StudentDashboard.php';</script>";
          } else {
            echo "<script>alert('Error! Try again later.');</script>";
          }
          //close the statement
          mysqli_stmt_close($stmt);
        }
      }
    ?>

    <main>
      <div>
        <h5 class="title" id="main-title">Payment Transaction:</h5>  
      </div>
      <?php 
        if(is_null($ret['trans_id'])){ ?>
          <p><small><strong>For payment information, you can pose a question in the chatbox below.</strong></small></p>
          <div class="form-group">
            <p><small><strong>NOTE: You have until <i><span style="color:darkred;"><?php echo date("F d, Y h:ia", strtotime($ret['expiry'])); ?></span></i> to settle your payment or you will have to re-apply for admission.</strong></small></p>
          </div> <?php
        } else {
          if ($ret['trans_status'] != 3) {
            //convert status to text
            $status = ($ret['trans_status'] == 1) ? "Pending" :  (($ret['trans_status'] == 2) ? "Approved" : null); ?>
          <div class="form-group">
            <p><small class="darkred">STATUS: <?php echo $status ?></small></p>
          </div> <?php 
          } 
        }
      ?>
      <div class="flex">
        <div class="modal-dialog">
          <div class="modal-content"> 
            <form id="trans_form" method="post" enctype="multipart/form-data">
              <div class="modal-body">
                <div class="form-group">
                  <label class="form-label">Mode of Payment</label>
                  <select id="mode" name="mode" class="form-control" required>
                    <option value="" hidden="">Select mode of payment...</option>
                    <option value="Cashier" <?php if($ret['mode'] == "Cashier") echo 'selected'; ?>>Cashier</option>
                    <option value="GCash to GCash" <?php if($ret['mode'] == "GCash to GCash") echo 'selected'; ?>>GCash to GCash</option>
                    <option value="GCash to China Bank" <?php if($ret['mode'] == "GCash to China Bank") echo 'selected'; ?>>GCash to China Bank</option>
                    <option value="Bank to Bank Transfer" <?php if($ret['mode'] == "Bank to Bank Transfer") echo 'selected'; ?>>Bank to Bank Transfer</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Reference Number</label>
                  <input id="ref" name="ref" type="text" class="form-control" value="<?php echo $ret['ref_num'];?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Transaction Date</label>
                  <input name="date" max="<?php echo date('Y-m-d') ?>" type="date" class="form-control" value="<?php echo $ret['trans_date'];?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Name of Sender</label>
                  <input name="sender" type="text" class="form-control" value="<?php echo $ret['sender'];?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Amount</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">₱</span>
                    </div>
                    <input name="amount" id="amount" type="number" min="1" class="form-control" value="<?php echo $ret['amount'];?>" required>
                  </div>
                </div>
                <div id="resend_div"
                  <?= $ret['trans_id'] ? 'style="display: none"' : '' //if id is not null hide this ?>>
                  <div class="form-group">
                    <label class="form-label">Screenshot/Image of Transaction</label>
                    <div class="custom custom-file">
                      <input id="proof" name="proof" onchange="checkImage(event)" type="file" class="custom-file-input" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button id="add" name="add" type="submit" class="btn btn-success">Add</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        <?php if ($ret['trans_status'] == 3) { 
          // if transaction rejected show remarks and resend option here ?>
          <div class="modal-dialog stat">
            <div class="modal-content"> 
              <div class="form-group">
                <p><small class="darkred">STATUS: Rejected</small></p>
              </div>
              <div class="form-group">
                <label class="form-label">Remarks</label>
                <textarea rows="5" id="stat" name="stat" class="form-control"><?php echo $ret['trans_remarks']; ?></textarea>
              </div>
              <div class="modal-footer">
                  <button id="resend" name="resend" type="button" class="btn btn-success" onclick="clearForm()">Resend Transaction</button>
              </div>
            </div>
          </div> <?php 
        } ?>
      </div>
    </main>
    <script type="text/javascript">
      function clearForm() {
        // clear fields and make editable
        const formElements = document.querySelectorAll('#trans_form input, #trans_form select, #trans_form textarea');
        for (let i = 0; i < formElements.length; i++) {
          formElements[i].value = '';
          formElements[i].style.pointerEvents = "auto";
        }
        $('.stat').hide();
        $('#resend_div').show();
      }

      function checkImage(event) {
        //check type and size of proof of transaction image
        const input = document.getElementById('proof');
        const file = event.target.files[0];
        const reader = new FileReader();
        if (!file || !file.type.match(/image\/(png|jpe?g|jfif)/)) {
          alert("Only PNG, JPG, JPEG, and JFIF files are allowed!");
          input.value = '';
          return;
        }
        if (file.size > 2 * 1024 * 1024) {
          alert("Image size exceeds 2MB limit!");
          input.value = '';
          return;
        }
      }
    </script>

     <!-- Messenger Chat Plugin Code -->
    <div id="fb-root"></div>

    <!-- Your Chat Plugin code -->
    <div id="fb-customer-chat" class="fb-customerchat">
    </div>

    <script>
      var chatbox = document.getElementById('fb-customer-chat');
      chatbox.setAttribute("page_id", "105401405890404");
      chatbox.setAttribute("attribution", "biz_inbox");
    </script>

    <!-- Your SDK code -->
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          xfbml            : true,
          version          : 'v16.0'
        });
      };

      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>
  </body>
</html>

<style type="text/css">
  .darkred{
    color: #8b0000;
  }
  .modal-dialog{
    width: 40%;
    background: white;
    padding: 20px;
    margin: 0;
    border-radius: 10px;
    margin: 10px;
  }
  .stat{
    background: none;
  }
   .flex{
    display: flex;
  }
  .gap{
    justify-content: space-between;
  }
  .form-group{
    margin-bottom: 10px;
  }
  form img{
    border-bottom: 1px solid black;
  }
  #sendReq{
    font-size: 14px;
    margin: 2rem 0;
  }
  #sendReq *{
    font-family: Calibri;
  }
  .details_title{
    display: block;
    font-weight: bold;
    background: #d4edda;
    color:  #155724;
    padding: 5px;
    margin: 10px 0;
  }
  .right{
    text-align: right;
  }
  .center{
    text-align: center;
  }
  #input-format .input-fields{
  width: calc(100% / 2 - 15px);
  }
  #input-format .input-fields input{
    height: 40px;
    padding: 5px;
    background: white;
  }
  @media(max-width: 500px){
    .input-fields{
      width: 100% !important;
    }
    .flex{
      display: block;
    }
    .modal-dialog{
      width: 100% !important;
      margin: 0;
    }
    *{
      font-size: 12px;
    }
  } 
</style>