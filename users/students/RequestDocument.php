<?php
session_start();
error_reporting(0);

//navigation styles
$rdstyle = "active";

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
    	<title>ACTS Web Portal | Request Document</title>
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

  function showTable($uid){
      //show data on datatable
      global $table_contents, $con;
      $table_contents = "";
      
      $sql = "SELECT * FROM requests WHERE user_id = $uid";
      // execute query and generate table contents
      $result=$con->query($sql);
      while ($row = $result->fetch_assoc()) {
          $remarks = $row['req_remarks'];
          $req_id = $row['req_id'];
          // generate view button for proof
          $cancel_button = '<a class="cancel_button btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancel_modal" data-id="'.$req_id.'"><i class="las la-times"></a>';
          $cancel_button = str_replace("\r\n", "", $cancel_button); // remove new lines

          $view_button = '<a class="view_button btn btn-danger" data-bs-toggle="modal" data-bs-target="#remarks_modal" data-remarks="'.$remarks.'"><i class="las la-search"></a>';
          $view_button = str_replace("\r\n", "", $view_button); // remove new lines

          //convert status to text
          $req_status = ($row['req_status'] == 1) ? "Pending" : 
                        (($row['req_status'] == 2) ? "Processing" : 
                        (($row['req_status'] == 3) ? "Rejected" :
                        (($row['req_status'] == 4) ? "Fulfilled" : null)));
          //set background color based on status
          if ($req_status == "Processing") {
            $row_bgcolor = "style='background-color: #ffffe0'";
          } else {
            $row_bgcolor = "";
          }

          $table_contents = $table_contents."<tr ".$row_bgcolor.">
          <td>" .$row['doc_type']. "</td>
          <td>" .$row['copies']. "</td>
          <td>" .$row['purpose']. "</td>
          <td>" .date('F d, Y', strtotime($row['req_date']))."</td>
          <td>" .$req_status. "</td>
          <td>" .(($row['pickup_date'] != null) ? date('F d, Y', strtotime($row['pickup_date'])) : '') . "</td>
          <td>" .(($req_status == "Pending") ? $cancel_button : (($req_status == "Rejected") ? $view_button : '')). "</td>
        </tr>";
      }
    }
    showTable($uid);

    if (isset($_POST['req'])) {
      $type = $_POST['type'];
      $copies=$_POST['copies'];
      $purpose=$_POST['purpose'];
      if ($type=="Others") {
        $type=$_POST['others'];
      }
      $stmt = $con->prepare("INSERT INTO requests (user_id, doc_type, copies, purpose) VALUES ($uid,?,?,?)");
          $stmt->bind_param("sis", $type, $copies, $purpose);
          // Check for errors
          if($stmt->errno) {
            echo "Error: " . $stmt->error;
          }
          // Execute the statement
          if ($stmt->execute()) {
            echo "<script type='text/javascript'>document.location ='RequestDocument.php';</script>";
          } else {
            echo "<script>alert('Error! Try again later.');</script>";
          }
          //close the statement
          mysqli_stmt_close($stmt);
    }

   //cancel the request 
    if (isset($_POST['confirm'])) {
        $req_id = $_POST['r_id'];
        $sql = "DELETE FROM requests WHERE req_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $req_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script type='text/javascript'>document.location ='RequestDocument.php';</script>";
        } else {
            // Display an error message if the delete fails
            echo '<script>alert("Error occurred! Try again later");</script>';
        }
    }

  ?>
  	<main>
      <div class="title_div">
          <h5 class="title" id="main-title">Document Request:</h5>   
      </div>
      <div class="flex">
      <div id="request_form">
            <div class="modal-content"> 
              <form id="trans_form" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                  <div class="form-group">
                    <label class="form-label">Document Type</label>
                    <select id="type" name="type" class="form-control" required>
                      <option hidden="">Select type...</option>
                      <option>Copy of Grades</option>
                      <option >Transcript of Records (TOR)</option>
                       <option>Good Moral Certificate</option>
                      <option>Honorable Dismissal</option>
                      <option>Others</option>
                    </select>
                    <div id="others_div" style="display: none">
                      <input type="text" placeholder="(Specify the type of document)" name="others" id="others" class="form-control" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="form-label">No. of Copies</label>
                    <input id="copies" name="copies" max="10" placeholder="(max. of 10 copies)" type="number" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Purpose</label>
                    <textarea id="purpose" name="purpose" height="2" type="text" class="form-control" required></textarea>
                  </div>
                  <div class="modal-footer">
                    <button id="req" name="req" type="submit" class="btn btn-success">Request</button>
                  </div>
                </div>
              </form>
            </div>
      </div>
      <container>
        <p class="center"><small><strong>Request History</strong></small></p>
        <table id="tbl"  class="table table-hover">
          <thead class="table-success">
            <tr>
            <th>Document Type</th>
            <th>No. of Copies</th>
            <th>Purpose</th>
            <th>Date Requested</th>
            <th>Status</th>
            <th>Pickup Date</th>
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
            <h5 class="modal-title" id="myModalLabel">Are you sure to Cancel the Request?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="modal-form" method="post">
            <div class="modal-footer">
              <input type="hidden" name="r_id" id="r_id">
              <button id="cancel" name="cancel" type="button" data-bs-dismiss="modal" aria-label="Close" class="btn btn-secondary" >Close</button>
              <button id="confirm" name="confirm" type="submit" class="btn btn-success">Confirm</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    </main>
    <script type="text/javascript">
      $(document).on("click", ".view_button", function(){
        var remarks = $(this).attr("data-remarks");
        var url = $(this).attr("data-url");
        $("#remarks_txt").val(remarks);
        $("#view_proof").attr('data-url', url);
      });

      // set id of request to be canceled
      $(document).on("click", ".cancel_button", function(){
        var id = $(this).attr("data-id");
        $("#r_id").val(id);
      });

     $('#type').on('change', function() {
        let index = $('#type').prop('selectedIndex');
        if (index == 5) {
          $('#others_div').show();
          $('#others').prop('required', true);
        } else {
          $('#others_div').hide();
          $('#others').prop('required', false);
        }
      });
    </script>
  </body>
</html>
<style type="text/css">
  .flex{
    display: flex;
  }
  #request_form{
    width: 30%;
    background: white;
    padding: 20px;
    margin: 0;
    border-radius: 10px;
    margin: 10px;
  }
  container{
    padding: 15px;
  }
  table * {
    font-size: 14px;
  }
  .form-group{
    margin-bottom: 10px;
  }
  .center{
    text-align: center;
  }
  @media screen and (max-width: 860px) {
    .flex{
      display: block;
    }
    #request_form{
      width: 100%;
      margin: 0;
      margin-bottom: 10px;
    }
  }
</style>