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

// select count of enrolled students
$query=mysqli_query($con,"SELECT count(user_id) AS enrolled FROM student_list WHERE enrolled_status = 1");
$ret=mysqli_fetch_array($query);
$enrolled =  $ret['enrolled'];

//select count of pending and rejected applications
$query=mysqli_query($con,"SELECT count(case WHEN app_status = 1 THEN 1 END) AS pend_app, count(case WHEN app_status = 3 THEN 1 END) AS rej_app FROM applications");
$ret=mysqli_fetch_array($query);
$pend_app =  $ret['pend_app'];
$rej_app =  $ret['rej_app'];

//select count of pending and rejected transactions
$query=mysqli_query($con,"SELECT count(case WHEN trans_status = 1 THEN 1 END) AS pend_trans, count(case WHEN trans_status = 3 THEN 1 END) AS rej_trans FROM transactions");
$ret=mysqli_fetch_array($query);
$pend_trans =  $ret['pend_trans'];
$rej_trans =  $ret['rej_trans'];

//select count of pending and rejected document requests
$query=mysqli_query($con,"SELECT count(case WHEN req_status = 1 THEN 1 END) AS pend_req, count(case WHEN req_status = 3 THEN 1 END) AS rej_req FROM requests");
$ret=mysqli_fetch_array($query);
$pend_req =  $ret['pend_req'];
$rej_req =  $ret['rej_req'];




// Fetch announcements from the database
$fetchQuery = "SELECT * FROM announcements";
$announcementsResult = mysqli_query($con, $fetchQuery);
$announcements = mysqli_fetch_all($announcementsResult, MYSQLI_ASSOC);
$anncount = count($announcements);

// Output announcement data as JSON
echo '<script>';
echo 'var announcements = ' . json_encode($announcements) . ';';
echo '</script>';
?>




<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum scale=1">
	<title>ACTS Web Portal | Dashboard</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="tinymce/tinymce.min.js"></script>

	<script>
    tinymce.init({
        selector: '#myTextarea',
        height: 400, 
	
    });
    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: 'textarea.myTextarea1',
            height: 400,
            // Add more configuration options if needed
        });
    });
</script>
<link href="../../wysiwyg/css/style.css" rel="stylesheet" />
    <link href="../../wysiwyg/code-prettify-master/src/prettify.css" rel="stylesheet" />
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<link rel="stylesheet" href="../../css/navigation.css"/>
  	<link rel="stylesheet" href="../../css/style.css"/>
	<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!--icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
</head>
<body>
	<?php 
	//include navigationbar
	include('NavigationBar.php'); 
	$sy_id = $sy_ret['sy_id'];
	$sem_id = $sy_ret['sem_id'];
	$account_type = $ret['account_type'];

	//select count of pending students schedules
	$query=mysqli_query($con,"SELECT COUNT(s.student_num) AS pend_sub FROM student_list s LEFT OUTER JOIN applications a ON a.user_id=s.user_id WHERE a.category = 2 AND s.enrolled_status = 1  AND NOT EXISTS (SELECT 1 FROM student_subjects ss WHERE ss.student_num = s.student_num AND ss.school_year_id = $sy_id AND ss.sem_id = $sem_id);");
	$ret=mysqli_fetch_array($query);
	$pend_sub =  $ret['pend_sub'];

	?>

	<main>
		<div class="title_div">
        	<h5 class="title" id="main-title">Dashboard:</h5> 

        	 <!-- display active school year -->
	      	<h5 class="title sy">S.Y. <?php echo isset($sy_ret['sy_id']) ? $sy_ret['year_start']."-".$sy_ret['year_end']." " : '';?><small class="sy"> <?php echo isset($sy_ret['semester']) ? ($sy_ret['semester'] == 1 ? '1st Semester' : ($sy_ret['semester'] == 2 ? '2nd Semester' : '')) : ''; ?></small></h5>
      	</div>

		
			
    <div class="announcement-section">
    <div class="announcement-header">
        <h1>Announcements:</h1>
        <button class="btn btn-success addann" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">Add Announcement</button>
    </div>

    <?php
    // Check if there are announcements
    if (count($announcements) > 0) {
        echo '<div class="announcement-content">';

        // Add the carousel structure
        echo '<div id="announcementCarousel" class="carousel slide" data-bs-ride="carousel">';
        echo '<div class="carousel-inner">';

        foreach ($announcements as $key => $announcement) {
           
            echo "<div class='carousel-item announcement-item" . ($key === 0 ? " active" : "") . "' data-announcement-id='" . $announcement['id'] . "'>";
            echo "<div class='annhead0'>";
            echo "<div class='annhead1'>";
            echo "<div class='annhead'>";
            echo "<h3 class='announcement-title'>" . $announcement['announcement_title'] . "</h3>";
            echo "</div>";
            echo "<div class='annhead1'>";
            echo "<div class='buttonbox'>";
            echo "<button class='btn btn-primary edit-btn' data-bs-toggle='modal' data-bs-target='#editAnnouncementModal' data-announcement-id='" . $announcement['id'] . "'>Edit</button>";
            echo "<button class='btn btn-danger delete-btn' data-toggle='modal' data-target='#deleteConfirmationModal' data-announcement-id='" . $announcement['id'] . "'>Delete</button>";    
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";

            

            if ($announcement['announcement_type'] === 'image' && !empty($announcement['image_path'])) {
                echo $announcement['announcement_content'];

                echo "<div class='imgdiv'>";
                echo "<img src='" . $announcement['image_path'] . "' alt='Announcement Image' class='imagedisplay' >";
                echo"</div>";
            } else {
                echo $announcement['announcement_content'];
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

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" id="canceldel" class="btn-close canceldel" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this announcement?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary canceldel" id="canceldel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
    



		<div class="cards">
			<a href="<?php echo ($account_type == 3) ? 'StudentList.php' : '#'; ?>">
				<div class="card-single white" style="background: #003f18;">
					<div>
						<h1><?php echo($enrolled)?></h1>
						<span>Officially Enrolled</span>
					</div>
					<div>
						<span class="las la-user-check"></span>
					</div>
				</div>
			</a>
			<a href="Admissions.php?sta=1">
				<div class="card-single pending">
					<div>
						<h1><?php echo($pend_app)?></h1>
						<span>Pending Applications</span>
					</div>
					<div>
						<span class="las la-file-alt"></span>
					</div>
				</div>
			</a>
			<a href="Transactions.php?sta=1">
				<div class="card-single pending">
					<div>
						<h1><?php echo($pend_trans)?></h1>
						<span>Pending Transactions</span>
					</div>
					<div>
						<span class="las la-credit-card"></span>
					</div>
				</div>
			</a>
			<a href="DocumentRequests.php?sta=1">
				<div class="card-single pending">
					<div>
						<h1><?php echo($pend_req)?></h1>
						<span>Pending Document Requests</span>
					</div>
					<div>
						<span class="las la-print"></span>
					</div>
				</div>
			</a>
			<a href="SubjectsManager.php?sta=1">
				<div class="card-single pending">
					<div>
						<h1><?php echo($pend_sub)?></h1>
						<span>Pending Student Subjects</span>
					</div>
					<div>
						<span class="las la-book"></span>
					</div>
				</div>
			</a>
			<a href="Admissions.php?sta=3">
				<div class="card-single rejected">
					<div>
						<h1><?php echo($rej_app)?></h1>
						<span>Rejected Applications</span>
					</div>
					<div>
						<span class="las la-file-alt"></span>
					</div>
				</div>
			</a>
			<a href="Transactions.php?sta=3">
				<div class="card-single rejected">
					<div>
						<h1><?php echo($rej_trans)?></h1>
						<span>Rejected Transactions</span>
					</div>
					<div>
						<span class="las la-credit-card"></span>
					</div>
				</div>
			</a>
			<a href="DocumentRequests.php?sta=3">
				<div class="card-single rejected">
					<div>
						<h1><?php echo($rej_req)?></h1>
						<span>Rejected Document Requests</span>
					</div>
					<div>
						<span class="las la-print"></span>
					</div>
				</div>
			</a>
		</div>	

		<!-- Modal for adding announcement -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="announcementForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="announcementType">Announcement Type:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="announcementType" id="textType" value="text" checked required>
                            <label class="form-check-label" for="textType">Text</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="announcementType" id="imageType" value="image">
                            <label class="form-check-label" for="imageType">Image</label>
                        </div>
                    </div>
				

                    <div class="mb-3">
						<label id='anntitle' for="announcementTitle">Announcement Title:</label>
                        <input class="form-control" id="anntitle" name="announcementTitle" required></input>
                        <label id='anncontent' for="announcementContent">Announcement Content:</label>
            <!---- 
                        <div class="container">
			
			<div class="btn-toolbar" data-role="editor-toolbar" data-target="#editor">
				<div class="btn-group">
					<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Font Size"><i class="fa fa-text-height"></i>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a data-edit="fontSize 5" class="fs-Five">Huge</a></li>
						<li><a data-edit="fontSize 3" class="fs-Three">Normal</a></li>
						<li><a data-edit="fontSize 1" class="fs-One">Small</a></li>
					</ul>
				</div>
				<div class="btn-group">
					<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Font Color"><i class="fa fa-font"></i>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<p>&nbsp;&nbsp;&nbsp;Font Color:</p>
						<li><a data-edit="foreColor #000000">Black</a></li>
                        <li><a data-edit="foreColor #0000FF">Blue</a></li>
                        <li><a data-edit="foreColor #30AD23">Green</a></li>
						<li><a data-edit="foreColor #FF7F00">Orange</a></li>
						<li><a data-edit="foreColor #FF0000">Red</a></li>
						<li><a data-edit="foreColor #FFFF00">Yellow</a></li>
					</ul>
				</div>
				<div class="btn-group">
					<a class="btn btn-default" data-edit="bold" title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold"></i></a>
					<a class="btn btn-default" data-edit="italic" title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic"></i></a>
					<a class="btn btn-default" data-edit="strikethrough" title="Strikethrough"><i class="fa fa-strikethrough"></i></a>
					<a class="btn btn-default" data-edit="underline" title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline"></i></a>
				</div>
				<div class="btn-group">
					<a class="btn btn-default" data-edit="insertunorderedlist" title="Bullet list"><i class="fa fa-list-ul"></i></a>
					<a class="btn btn-default" data-edit="insertorderedlist" title="Number list"><i class="fa fa-list-ol"></i></a>
					<a class="btn btn-default" data-edit="outdent" title="Reduce indent (Shift+Tab)"><i class="fa fa-outdent"></i></a>
					<a class="btn btn-default" data-edit="indent" title="Indent (Tab)"><i class="fa fa-indent"></i></a>
				</div>
				<div class="btn-group">
					<a class="btn btn-default" data-edit="justifyleft" title="Align Left (Ctrl/Cmd+L)"><i class="fa fa-align-left"></i></a>
					<a class="btn btn-default" data-edit="justifycenter" title="Center (Ctrl/Cmd+E)"><i class="fa fa-align-center"></i></a>
					<a class="btn btn-default" data-edit="justifyright" title="Align Right (Ctrl/Cmd+R)"><i class="fa fa-align-right"></i></a>
					<a class="btn btn-default" data-edit="justifyfull" title="Justify (Ctrl/Cmd+J)"><i class="fa fa-align-justify"></i></a>
				</div>
				
					
				<div class="btn-group">
					<a class="btn btn-default" data-edit="undo" title="Undo (Ctrl/Cmd+Z)"><i class="fa fa-undo"></i></a>
					<a class="btn btn-default" data-edit="redo" title="Redo (Ctrl/Cmd+Y)"><i class="fa fa-repeat"></i></a>
				</div>
			</div>

            --->
			<textarea class="form-control" id="myTextarea" name="announcementContent" rows="3"></textarea>
			
            <!-----<div id="editorPreview"></div>
			
		</div>

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="../../wysiwyg/jquery.hotkeys-master/jquery.hotkeys.js"></script>
		<script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		<script src="../../wysiwyg/code-prettify-master/src/prettify.js"></script>
		<script src="../../wysiwyg/src/bootstrap-wysiwyg.js"></script>
		<script type='text/javascript'>
			$('#editor').wysiwyg();
			
			$(".dropdown-menu > input").click(function (e) {
        		e.stopPropagation();
    		});
			</script>

            --->
                    </div> 

                    <div class="mb-3">
                    <label id="imagelabel" for="imageInput" style="display:none">Insert Image: </label>
                    <input type="file" class="form-control mt-2" id="imageInput" name="imageInput" style="display:none;">
                    </div>

                    <div class="mb-3">
                        <label for="availability">Availability:</label>
                        <select class="form-select" id="availability" name="availability" required>
                            <option value="always">Always</option>
                            <option value="today">Today</option>
                            <option value="week">A Week</option>
                            <option value="month">A Month</option>
                            <option value="specificDate">Specific Date</option>
                        </select>
                    </div>

                    <div class="mb-3" id="specificDateContainer" style="display:none;">
                        <label for="specificDate">Specific Date:</label>
                        <input type="date" class="form-control" id="specificDate" name="specificDate">
                    </div>

                    <button type="submit" class="btn btn-success">Add Announcement</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for editing announcement -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAnnouncementForm" action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editAnnouncementId" name="announcementId">

                    <!-- Announcement Type -->
                    <div class="mb-3">
                        <label for="editAnnouncementType">Announcement Type:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="editAnnouncementType" id="editTextType" value="text" required>
                            <label class="form-check-label" for="editTextType">Text</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="editAnnouncementType" id="editImageType" value="image">
                            <label class="form-check-label" for="editImageType">Image</label>
                        </div>
                    </div>

                    <!-- Announcement Title -->
                    <div class="mb-3">
                        <label for="editAnnouncementTitle">Announcement Title:</label>
                        <input class="form-control" id="editAnnouncementTitle" name="announcementTitle" required>
                    </div>

                    <!-- Announcement Content -->
                    <div class="mb-3">
                        <label for="editAnnouncementContent">Announcement Content:</label>
                        <textarea class="form-control myTextarea1" id="editAnnouncementContent" name="announcementContent" rows="3"></textarea>
                    </div>

                    <!-- Insert Image Input -->
                    <div class="mb-3" id="editImageContainer" style="display:none;">
                        <label id="imagelabel" for="editImageInput">Insert Image:</label>
                        <input type="file" class="form-control" id="editImageInput" name="imageInput">
                    </div>

                    <!-- Availability -->
                    <div class="mb-3">
                        <label for="editAvailability">Availability:</label>
                        <select class="form-select" id="editAvailability" name="availability" required>
                            <option value="always">Always</option>
                            <option value="today">Today</option>
                            <option value="week">A Week</option>
                            <option value="month">A Month</option>
                            <option value="specificDate">Specific Date</option>
                        </select>
                    </div>

                    <!-- Specific Date Input -->
                    <div class="mb-3" id="editSpecificDateContainer" style="display:none;">
                        <label for="editSpecificDate">Specific Date:</label>
                        <input type="date" class="form-control" id="editSpecificDate" name="specificDate">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
					
	</main>	
	<script>
    $(document).ready(function () {

        

        $('input[type=radio][name=editAnnouncementType]').change(function () {
        if (this.value == 'image') {
            $('#editImageContainer').show();
        } else {
            $('#editImageContainer').hide();
        }
    });

    // Show/hide specific date input in edit modal based on availability
    $('#editAvailability').change(function () {
        if (this.value == 'specificDate') {
            $('#editSpecificDateContainer').show();
        } else {
            $('#editSpecificDateContainer').hide();
        }
    });

    // Handle edit button click event
$(document).on('click', '.edit-btn', function () {
    var announcementId = $(this).data('announcement-id');
    var announcement = getAnnouncementById(announcementId);
    var imagePath = $(this).data('image-path');
    if (announcement) {
        // Log announcement object for debugging
        console.log('Announcement:', announcement);

        // Populate the modal with announcement details
        $('#editAnnouncementId').val(announcement.id);
        $('#editAnnouncementTitle').val(announcement.announcement_title);
        $('#editAnnouncementContent').val(announcement.announcement_content);
        $('#editAvailability').val(announcement.availability);
        $('#editImageInput').val(imagePath);

        // Set announcement type radio button based on the announcement type
        if (announcement.announcement_type === 'image') {
            $('#editImageType').prop('checked', true);
            $('#editImageContainer').show();
        } else {
            $('#editTextType').prop('checked', true);
            $('#editImageContainer').hide();
        }

        // Show/hide image input in edit modal based on announcement type
    $('input[type=radio][name=editAnnouncementType]').change(function () {
        if (this.value == 'image') {
            $('#editImageContainer').show();
        } else {
            $('#editImageContainer').hide();
        }
    });

        $('#editAnnouncementModal').modal('show');
    } else {
        console.log('Error: Announcement not found.');
    }
});

    // Handle form submission for editing announcement
    $('#editAnnouncementForm').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        var announcementType = $('input[name="editAnnouncementType"]:checked').val();
         formData.append('announcementType', announcementType);

        $.ajax({
            type: 'POST',
            url: 'EditAnnouncement.php', // Update the URL for handling edit operation
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response === 'success') {
                    
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function (error) {
                console.log('Error:', error);
                alert('Error: ' + error.responseText);
            }
        });
    });

   // Function to retrieve announcement details by ID
   function getAnnouncementById(announcementId) {
        // Iterate through announcements array to find the one with matching ID
        for (var i = 0; i < announcements.length; i++) {
            if (announcements[i].id == announcementId) {
                return announcements[i];
            }
        }
        return null; // Return null if announcement not found
    }

       
        $(document).on('click', '.delete-btn', function () {
    var announcementId = $(this).data('announcement-id');

    console.log('Debug: announcementId=' + announcementId);

    if (announcementId !== undefined) {
        // Open the confirmation modal
        $('#deleteConfirmationModal').modal('show');

        // Set the data-announcement-id attribute for the confirmation button
        $('#confirmDeleteBtn').attr('data-announcement-id', announcementId);
    } else {
        console.log('Error: Announcement ID is undefined.');
    }
});



$('.canceldel').on('click',function(){
    $('#deleteConfirmationModal').modal('hide');
});


$('#confirmDeleteBtn').click(function () {
    var announcementId = $(this).data('announcement-id');

    // Make an AJAX request to delete the announcement
    $.ajax({
        type: 'POST',
        url: 'DeleteAnnouncement.php',
        data: { announcementId: announcementId },
        success: function (response) {
            console.log('Success:', response);
            // Handle the success response
            if (response === 'success') {
                // Reload the page or update the announcements without refreshing the page
                location.reload();
            } else {
                // Show error alert
                alert('Error: ' + response);
            }
        },
        error: function (error) {
            console.log('Error:', error.responseText);  // Log the actual error response
            // Handle the error (e.g., display an alert)
            alert('Error: ' + error.responseText);
        }
    });

    // Close the confirmation modal
    $('#deleteConfirmationModal').modal('hide');
});


        // Show/hide image input and set required attribute based on radio button selection
        $('input[type=radio][name=announcementType]').change(function () {
            if (this.value == 'image') {
                $('#imageInput,#editImageInput').prop('required', true);
                $('#textInput').prop('required', false);
                $('#imagelabel').show();
                $('#imageInput,#editImageInput').show();
                $('#textAreaContainer').hide();
                $('#anncontent').text("Announcement Content (Optional):");
                $('#anncontent').prop('required',false)
            } else {
                $('#imageInput,#editImageInput').prop('required', false);
                $('#textInput').prop('required', true);
                $('#imagelabel').hide();
                $('#imageInput,#editImageInputwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww').hide();
                $('#textAreaContainer').show();
                $('#anncontent').text("Announcement Content:");
                $('#anncontent').prop('required',true)
            }
        });

        // Show/hide specific date input based on dropdown selection
        $('#availability').change(function () {
            if (this.value == 'specificDate') {
                $('#specificDateContainer').show();
            } else {
                $('#specificDateContainer').hide();
            }
        });

		$('#addAnnouncementModal').on('hidden.bs.modal', function () {
        $('#announcementForm')[0].reset();
        $('input[type=radio][name=announcementType]').prop('checked', false);
        $('#imageInput').hide();
        $('#imagelabel').hide();
        $('#textAreaContainer').show();
        $('#specificDateContainer').hide();
    });
    

        // Submit form logic (using AJAX)
$('#announcementForm').submit(function (e) {
    e.preventDefault();

    // Serialize form data
    var formData = new FormData(this);
  

    $.ajax({
        type: 'POST',
        url: 'AddAnnouncement.php', 
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
    // Handle the response
    if (response) {
        var announcementId = response.announcementId;
        var editButtonHtml = response.editButtonHtml;
        
        var newAnnouncement = "<div class='carousel-item'>";
		newAnnouncement += "<h4 class='addsucc'>Successfully Added New Announcement</h4>";
        newAnnouncement += "<div class='annhead0'>";
        newAnnouncement +=  "<div class='annhead1'>";
        newAnnouncement +="<div class='annhead'>";  
        newAnnouncement += "<h3>" + formData.get('announcementTitle') + "</h3>";   
        newAnnouncement += "</div>";
        newAnnouncement += "<div class='annhead1'>";
        newAnnouncement += "<div class='buttonbox'>";
        console.log(response);
        newAnnouncement += "<button class='btn btn-primary edit-btn' data-bs-toggle='modal' data-bs-target='#editAnnouncementModal' data-announcement-id='" +response+ "'>Edit</button>";  
        newAnnouncement += "<button class='btn btn-danger delete-btn' data-toggle='modal' data-target='#deleteConfirmationModal' data-announcement-id='" +response+ "'>Delete</button>";  
        newAnnouncement += "</div>";
        newAnnouncement += "</div>";
        newAnnouncement += "</div>";
        newAnnouncement += "</div>";
        newAnnouncement += "<p>" + formData.get('announcementContent') + "</p>";

        var announcementType = $('input[name="announcementType"]:checked').val();
        if (announcementType === 'image') {
        var fileInput = formData.get('imageInput');
        if (fileInput instanceof File) {
            var imageURL = URL.createObjectURL(fileInput);
            newAnnouncement += " <div class='imgdiv'>";
            newAnnouncement += "<img src='" + imageURL + "' alt='Announcement Image' class='imagedisplay'>";
            newAnnouncement += " </div>";
        }
    }
        
        newAnnouncement += "</div>";
        
        $('.carousel-inner').append(newAnnouncement);
        $('#announcementCarousel').carousel("pause");
        var newIndicator = $('.announcement-indicators span:last-child').clone()
        newIndicator.attr("data-bs-slide-to",$('.announcement-indicators').children().length)
        
        $('.announcement-indicators').append(newIndicator);
        
        $('#announcementCarousel').carousel($('.announcement-indicators').children().length-1);
        
        $('#announcementCarousel').on('slid.bs.carousel', function () {
        // Hide the success message when the carousel slides
        setTimeout(function() {
        $('.addsucc').hide();
    }, 2500);
    setTimeout(function() {
        location.reload();
    }, 2500);
    });
    
        
        // Clear the form
        $('#announcementForm')[0].reset();

        // Close the modal after successful submission
        $('#addAnnouncementModal').hide();
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        } else {
        // Show error alert
        alert('Error: ' + response);
    }
},
        error: function (error) {
            // Handle the error (e.g., display an alert)
            console.log('Error:', error);
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
	.white * {
		color: #fff;
	}
	.rejected{
		background: #ffcccb
	}
	.pending{
		background: #cdfacf
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

/* Modal styles */
.modal-content {
    border-radius: 8px;
}

.modal-header {
    border-bottom: 1px solid #ccc;
    border-radius: 8px 8px 0 0;
}

.modal-title {
    font-size: 1.2em;
    color: #333;
}

.modal-body {
    padding: 15px;
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

.carousel-item img{
    width: auto;
    height:100%;
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
.annhead{
    width:100%;
    display:flex;
    justify-content: space-between;
    align-items: center;
    flex-direction:row;
    position:relative;
}
.delete-btn{
    background-color:#d9534f !important;
    border-color:#d43f3a !important;
    color: #fff !important;
}
.edit-btn{
    
    margin-right: 10px; 
}
.buttonbox{
    position:absolute;
    top:0;
    right:70;
    display:flex;
    justify-content:flex-end;
    align-items: center;
  
    width:auto;
    height:auto;
}

.annhead1{
    float: left;
  width: 85%;
  padding: 0px 0px 7px 0px ;
}
.annhead0:after {
  content: "";
  display: table;
  clear: both;
}
@media only screen and (max-width: 768px) {
    .announcement-section {
        width: 90%; /* Adjust the width as needed */
    }

    .announcement-item,
    .carousel-item {
         text-align:justify;
        padding: 0 20px; /* Adjust the padding as needed */
    }

    .buttonbox {
        right: 20px; /* Adjust the right positioning of the buttons */
    }
}

@media only screen and (max-width: 576px) {
    .announcement-section {
        width: 100%; /* Adjust the width as needed */
    }

    .carousel-item,
    .announcement-item {
        text-align:justify;
        padding: 0 45px; /* Adjust the padding as needed */
    }
    .carousel-control-prev,
.carousel-control-next {
    width:12%;
    }

   .buttonbox {
        display: flex; /* Use flexbox for centering */
        justify-content: center; /* Center items horizontally */
        align-items: center; /* Center items vertically */
        margin-top: 10px; /* Add margin as needed */
        position: relative; /* Reset positioning */
        right: auto; /* Reset right positioning */
    }

    .buttonbox .btn {
        margin: 5px; /* Adjust margin between buttons */
    }

    .imgdiv {
        height: auto; /* Reset height */
    }
}

</style>