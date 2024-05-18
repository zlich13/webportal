<?php 
  session_start();
  error_reporting(0);

  //navigation styles
  $etstyle = "active";

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
    <title>ACTS Web Portal | Payment Transactions</title>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- styles -->
    <link rel="stylesheet" href="../../css/navigation.css"/>
    <link rel="stylesheet" href="../../css/style.css"/>
    <!-- icons -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
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


function showTransactions($user_id) {
    //show data on datatable based on mode and status filter
    global $table_contents, $con;
    $table_contents = "";
    $sql = "SELECT * FROM transactions WHERE user_id = '$user_id' ";
    // execute SQL query and generate table contents
    $result = $con->query($sql); // execute SQL query and store result in $result variable
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
      $remarks = $row['trans_remarks'];
      $trans_id = $row['trans_id'];

      // generate view button for remarks
      $view_remarks = '<button  type="button" class="view_remarks btn btn-danger" data-bs-toggle="modal" data-bs-target="#remarks_modal" data-remarks="'.$remarks.'"><i class="las la-search"></i></button>';
      $view_remarks = str_replace("\r\n", "", $view_remarks); // remove new lines

       // generate cancel button for proof
      $cancel_button = '<button type="button" class="cancel_button btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancel_modal" data-id="'.$trans_id.'"><i class="las la-times"></i></button>';
      $cancel_button = str_replace("\r\n", "", $cancel_button); // remove new lines

        $table_contents .= "<tr>
          <td>" . $row['ref_num'] . "</td>
          <td>" . $row["mode"] . "</td>
          <td>" . $row["sender"] . "</td>
          <td>" . $row["amount"] . "</td>
          <td>" . $row["purpose"] . "</td>
           <td>" . $row["trans_date"] . "</td
           <td>" . $row["status"] . "</td>";

           if ($row['trans_status'] == 1) {
        // if pending show view proof, approve and reject button
        $table_contents =  $table_contents."
        <td>" .$cancel_button. "</td>
        </tr>";
        } else if ($row['trans_status'] == 3){
        // if rejected show view remarks button
        $table_contents =  $table_contents."
        <td>" . $view_remarks . "</td>
        </tr>";
      } else {
        //show nothing
        $table_contents =  $table_contents."
        <td>Approved</td>
        </tr>";
      }
      }
    }
}
showTransactions($uid);

      // add transaction
      if (isset($_POST['add'])) {
        $mode = $_POST['mode'];
        $ref = $_POST['ref'];
        $date = $_POST['date'];
        $sender = $_POST['sender'];
        $amount = $_POST['amount'];
        $purpose = $_POST['purpose'];

        $tmpName = $_FILES['proof']['tmp_name'];
        $proof = base64_encode(file_get_contents(addslashes($tmpName)));

          //insert new transaction 
          $stmt = $con->prepare("INSERT INTO transactions (user_id, mode, ref_num, trans_date, sender, amount, proof, purpose) VALUES ($uid,?,?,?,?,?,?,?)");
          $stmt->bind_param("ssssiss", $mode, $ref, $date, ucwords($sender), $amount, $proof, $purpose);
          // Check for errors
          if($stmt->errno) {
            echo "Error: " . $stmt->error;
          }
          // Execute the statement
          if ($stmt->execute()) {
            echo "<script type='text/javascript'>document.location ='EnrolledTransaction.php';</script>";
          } else {
            echo "<script>alert('Error! Try again later.');</script>";
          }
          //close the statement
          mysqli_stmt_close($stmt);
        }

         // cancel transaction
    if (isset($_POST['confirm'])) {
        $c_trans_id = $_POST['t_id'];
        $sql = "DELETE FROM transactions WHERE trans_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $c_trans_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script type='text/javascript'>document.location ='EnrolledTransaction.php';</script>";
        } else {
            // Display an error message if the delete fails
            echo '<script>alert("Error occurred! Try again later");</script>';
        }
    }

    ?>

    <main>
      <div>
        <h5 class="title" id="main-title">Payment Transactions:</h5>  
      </div>
          <p><small>For payment information, you can pose a question in the chatbox below.</small></p>
      <div class="flex">
        <div id="payment_form">
          <div class="modal-content"> 
            <form id="trans_form" method="post" enctype="multipart/form-data">
              <div class="modal-body">
                <div class="form-group">
                  <label class="form-label">Mode of Payment</label>
                  <select id="mode" name="mode" class="form-control" required>
                    <option value="" hidden="">Select mode of payment...</option>
                    <option value="Cashier" >Cashier</option>
                    <option value="GCash to GCash" >GCash to GCash</option>
                    <option value="GCash to China Bank" >GCash to China Bank</option>
                    <option value="Bank to Bank Transfer" >Bank to Bank Transfer</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Reference Number</label>
                  <input id="ref" name="ref" type="text" class="form-control" value="" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Transaction Date</label>
                  <input name="date" max="<?php echo date('Y-m-d') ?>" type="date" class="form-control" value="" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Name of Sender</label>
                  <input name="sender" type="text" class="form-control" value="" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Amount</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">₱</span>
                    </div>
                    <input name="amount" id="amount" type="number" min="1" class="form-control" value="" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Purpose</label>
                  <input name="purpose" type="text" class="form-control" value="" required>
                </div>
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
            </form>
          </div>
        </div>
        <container>
        <p class="center"><small><strong>Transaction History</strong></small></p>
        <table id="tbl"  class="table table-hover">
          <thead class="table-success">
            <tr>
            <th>Reference#</th>
            <th>Mode</th>
            <th>Sender</th>
            <th>Amount</th>
            <th>Purpose</th>
            <th>Date</th>
            <th></th>
            </tr>
          </thead>
          <tbody>
            <?php echo "$table_contents";?>
          </tbody>
        </table> 
      </container>
        <div class="modal" id="remarks_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Remarks</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <textarea name="remarks_txt" id="remarks_txt" class="form-control" rows="3" disabled></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
      <div class="modal fade" id="cancel_modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
             <h5 class="modal-title" id="myModalLabel">Are you sure to Cancel Transaction?</h5>
          </div>
         <div class="modal-footer">
          <form method="post">
              <input type="hidden" name="t_id" id="t_id">
              <button id="cancel" name="cancel" type="button" data-bs-dismiss="modal" aria-label="Close" class="btn btn-secondary" >Close</button>
                <button type="submit" class="btn btn-success" id="confirm" name="confirm">Confirm</button>
                </form>
            </div>
        </div>
      </div>
    </div>
    </main>
    <script type="text/javascript">
     $(document).ready(function() {
       $(document).on("click", ".view_remarks", function(){
        var remarks = $(this).attr("data-remarks");
        $("#remarks_txt").val(remarks);
      });

        // set id of request to be canceled
      $(document).on("click", ".cancel_button", function(){
        var id = $(this).attr("data-id");
        $("#t_id").val(id);
      });

    });

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
      chatbox.setAttribute("page_id", "170613899457651");
      chatbox.setAttribute("attribution", "biz_inbox");
    </script>

    <!-- Your SDK code -->
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          xfbml            : true,
          version          : 'v18.0'
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
  #payment_form{
    width: 30%;
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
  container{
    padding: 15px;
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
    *{
      font-size: 12px;
    }
  }
   @media screen and (max-width: 860px) {
    .flex{
      display: block;
    }
    #payment_form{
      width: 100%;
      margin: 0;
      margin-bottom: 10px;
    }
  }
</style>