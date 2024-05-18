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
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | SMS</title>
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

?>

	<main>
		<div>
      <h5 class="title" id="main-title">Custom Message:</h5>  
    </div>
<!-- The modal -->
  <br>
    <div id="myModal" style="width: 50%">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="modal-form" method="post">
            <div class="modal-body">
              <div>
                <label for="numbers" class="form-label">Send to:</label>
                <input type="text" class="form-control" placeholder = "Input mobile phone number/s here"name="numbers" id="numbers" required>
              </div>
              <div style="text-align: right;">
                <p><small><i>Multiple phone numbers should be separated by comma</i></small></p>
              </div>
              <br>
              <div class="mb-3">
                <textarea id="message" rows="6" name="message" class="form-control" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <div>
                <button id="send_message" name="send_message" type="submit" class="btn btn-success">Send</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
<style type="text/css">
  #form {
    display: flex;
    justify-content: space-between;
  }

  #form button {
    margin: 5px;
  }
</style>