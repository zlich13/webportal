<?php 
date_default_timezone_set('Asia/Manila');
session_start();
error_reporting(0);

$smsstyle = "active";

	//connect to database
	include('../../dbconnection.php');

	// Redirect to logout page if no user logged in
  if (empty($_SESSION['uid'])) {
    header('Location: ../../logout.php');
    exit;
  }

require_once '../../PhpSpreadsheet-1.28.0/src/PhpSpreadsheet/IOFactory.php';
require '../../PhpSpreadsheet-1.28.0/vendor/autoload.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>Acts Web Portal | SMS</title>
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
//include navigationbar
include('NavigationBar.php');

if (isset($_POST['send_message'])) {
    // Set your Semaphore API key
    $apiKey = 'insert_semaphore_api_key_here';

    // Set the recipient numbers and message
    $numbers = $_POST['numbers'];
    $message = $_POST['message'];

    // Create the cURL request
    $ch = curl_init();

    // Set the request URL
    $url = 'https://semaphore.co/api/v4/messages';

    // Set the POST data
    $postData = array(
        'apikey' => $apiKey,
        'number' => $numbers,
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
          echo "<script>alert('There is an invalid number. Messages not sent');</script>";
        } else {
          echo "<script>alert('Message sent successfully to numbers.'');</script>";
        }
    }
    // Close cURL resource
    curl_close($ch);
}

// Set the initial date if it hasn't been set yet
if(!isset($_SESSION['current_date'])) {
    $_SESSION['current_date'] = date("Y-m-d");
}


function showTable($this_date, $status) {
    date_default_timezone_set('Asia/Manila');
    global $table_contents, $con;
    $table_contents = "";

    $sql = "SELECT att.*, s.student_year, s.student_course, c.acronym, a.lname, a.fname, a.mname, a.guardian_phone FROM attendance att LEFT OUTER JOIN student_list s ON att.student_num = s.student_num LEFT OUTER JOIN course_strand c ON s.student_course=c.id LEFT OUTER JOIN applications a ON s.user_id = a.user_id WHERE a.is_gp_verified = 1 AND att.attend_date = '$this_date'";

    if ($status !== 'All') {
          $sql = $sql . " AND att.status = '$status'";
    }

    $result = $con->query($sql);
    while ($row = $result->fetch_assoc()) {
        $table_contents = $table_contents . "<tr>
        <td>" . $row["student_num"] . "</td>
        <td>" . $row["lname"] . " " . $row['fname'] . " " . $row['mname'] . "</td>
        <td>" . $row["student_year"] . "</td>
        <td>" . $row["acronym"] . "</td>
        <td>" . (($row["status"] == 1) ? "Absent" : "Late") . "</td>
        <td>" . $row["guardian_phone"] . "</td>
        <td><div class='sets flex'><input type='checkbox' class='sets_cb'></div></td>
        </tr>";
    }
}


showTable($_SESSION['current_date'],'All');

if(isset($_POST['status'])){
  $status = $_POST['status'];
  showTable($_SESSION['current_date'], $status);
}

// Update the date based on the button that was clicked
if(isset($_POST['increase'])) {
    $_SESSION['current_date'] = date('Y-m-d', strtotime($_SESSION['current_date'] . ' + 1 days'));
} elseif(isset($_POST['decrease'])) {
    $_SESSION['current_date'] = date('Y-m-d', strtotime($_SESSION['current_date'] . ' - 1 days'));
} elseif(isset($_POST['today'])) {
    $_SESSION['current_date'] = date("Y-m-d");
}


if(isset($_POST['increase']) || isset($_POST['decrease']) || isset($_POST['today'])) {
    showTable($_SESSION['current_date'], $status);
}

// Check if the current date is today
$is_current_date = ($_SESSION['current_date'] == date("Y-m-d"));

$row_date = $_SESSION['current_date'];

// Check if a file was uploaded
if(isset($_FILES['file-upload']) && $_FILES['file-upload']['error'] === UPLOAD_ERR_OK) {
    
    // Get file name and path
    $inputFileName = $_FILES['file-upload']['tmp_name'];
    
    try {
        // Read the spreadsheet
        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        
        // Get the first worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Get the highest row and column numbers used in the worksheet
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        // Loop through each row of the worksheet
    for ($row = 2; $row <= $highestRow; $row++) {
        
        // Read a row of data into an array
        $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        
        // Get the values from the array
        $row_s_num = $rowData[0][0];
        $row_status = $rowData[0][1];
        
          $stmt = $con->prepare("INSERT INTO attendance (student_num, status, attend_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $row_s_num, $row_status, $row_date);

        // Execute the statement and check for errors
        if (!$stmt->execute()) {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    }
    echo "<script type='text/javascript'>document.location ='AbsentsTardy.php';</script>";
    } catch (Exception $e) {
        echo 'Error loading file: ',  $e->getMessage(), "\n";
    }
}
?>

	<main>
    <div class="flex gap">
      <div>
      <h5 class="title" id="main-title">Absentees/Tardies:</h5>  
    </div>
        <form method="post" enctype="multipart/form-data">
                  <label for="file-upload" class="custom-file-upload">Upload Attendance</label>
                  <input id="file-upload" name="file-upload" onchange="checkFile(event)" type="file" style="display: none;"/>
            </form>
            
      </div>
    <div>
      <h3 class="title sy" id="current-date"><?php echo date('F d, Y', strtotime($_SESSION['current_date'])); ?></h3>
    </div>
	  <form method="post" id="form" class="flex gap" onsubmit="disableButton()">
      <div id="chooser_div">
        <button class="btn btn-success" type="submit" name="decrease" id="decrease">&lt;</button>
        <button class="btn btn-success" type="submit" name="today">Today</button>
        <button class="btn btn-success" type="submit" name="increase" id="increase"<?php echo ($is_current_date) ? 'disabled' : ''; ?>>&gt;</button>
      </div>
      <div class="flex gap">
      <div>
        <select id="status" name="status" style="height: 35px; width: 150px;" onchange="this.form.submit()">
          <option value="All" <?php if(isset($_POST['status']) && $_POST['status'] == 'All') echo 'selected';?>>All</option>
          <option value="1" <?php if(isset($_POST['status']) && $_POST['status'] == '1') echo 'selected';?>>Absent</option>
          <option value="2" <?php if(isset($_POST['status']) && $_POST['status'] == '2') echo 'selected';?>>Late</option>
        </select>
      </div>
      <div id="send_div">
        <button class="btn btn-success" type="button" name="send" id="send" data-bs-toggle="modal" data-bs-target="#myModal">Send Message</button> 
      </div>
      </div>
    </form>
    <div id="tables_div">
    <table id="tbl" class="table table-hover">
      <thead class="table-success">
        <tr>
          <th>Student #</th>
          <th>Name</th>
          <th>Year</th>
          <th>Course</th>
          <th>Status</th>
          <th>Guardian's #</th>
          <th>
            <div class="sets">
              Send to All<input type="checkbox" id="checkAllCb">
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <?= $table_contents ?>
      </tbody>
    </table>
  </div>
  <!-- The modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title for_add" id="myModalLabel">Message:</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="modal-form" method="post">
            <div class="modal-body">
              <div class="mb-3">
                <textarea id="message" rows="4" name="message" class="form-control" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <div class="modal-flex">
                <small>Sent to (<span id="numPhones"></span>) users.</small>
                <input type="hidden" name="numbers" id="numbers">
                <button type="button" class="btn btn-danger " data-bs-dismiss="modal">Cancel</button>
                <button id="send_message" name="send_message" type="submit" class="btn btn-success">Send</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
 <script type="text/javascript">
  $(document).ready(function() {
    // Initialize the data table
    $('#tbl').DataTable({
      columnDefs: [
        { orderable: false, targets: [5, 6] }
      ],
      dom: '<"my-custom-element">t<"bottom"i>p<"clear">',
      initComplete: function() {
        $('.my-custom-element').prepend($('#form'));
      }
    });

  // Handle checkboxes and local storage
  var $setsCb = $('.sets_cb');
  var $checkAllCb = $('#checkAllCb');
  var $numPhones = $('#numPhones');
  var $numbers = $('#numbers'); 

  var storedPhones = localStorage.getItem('phones') || '[]';
  var phones = JSON.parse(storedPhones);
  var numPhones = phones.length;

  $setsCb.change(function() {
    updateLocalStorage();
  });

  $checkAllCb.change(function() {
    $setsCb.prop('checked', this.checked);
    updateLocalStorage();
  });

  function updateLocalStorage() {
    var phones = $setsCb.filter(':checked').map(function() {
      return $(this).closest('tr').find('td:eq(5)').text();
    }).get();

    localStorage.setItem('phones', JSON.stringify(phones));
    numPhones = phones.length;
    $numPhones.html(numPhones);

    var selectedPhones = phones.join(', ');
    $numbers.val(selectedPhones);
  }

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
</body>
</html>
<style type="text/css">
  .flex{
    display: flex;
  }
  .gap{
    justify-content: space-between;
  }
  .sets input {
    margin: 0;
    margin-left: 10px;
    height: 24px;
    width: 20px;
  }

  .sets input:hover {
    cursor: pointer;
  }
  #form button, #form select {
    margin: 5px;
  }

  .sy {
    text-align: center;
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

</style>