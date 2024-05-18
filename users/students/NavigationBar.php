<?php
	session_start();
	error_reporting(0);

	// Connect to database
	include '../../dbconnection.php';

	// Redirect to logout page if no user logged in
	if (empty($_SESSION['uid'])) {
	    header('Location: ../../logout.php');
	    exit;
	}
	
	date_default_timezone_set('Asia/Manila');
	
	//select user logged in
	$uid = $_SESSION['uid'];
	$query=mysqli_query($con,"SELECT u.*, a.*, t.*, s.*, c.acronym, c.name, s_sec.section_id, sec.name AS sec_name FROM user_accounts u LEFT OUTER JOIN applications a ON u.id = a.user_id LEFT OUTER JOIN transactions t ON u.id = t.user_id LEFT OUTER JOIN student_list s ON u.id = s.user_id LEFT JOIN course_strand c ON a.course = c.id LEFT OUTER JOIN student_section s_sec ON s.student_num=s_sec.student_num LEFT OUTER JOIN sections sec ON s_sec.section_id=sec.section_id WHERE u.id = $uid");
	$ret=mysqli_fetch_array($query);

	$info_query=mysqli_query($con,"SELECT * FROM school_info where id = 1");
	$info_ret=mysqli_fetch_array($info_query);

	$sy_query=mysqli_query($con,"SELECT sy.*, sem.* FROM school_years sy JOIN semesters sem ON sy.sy_id=sem.sy_id WHERE sy.is_active = 1 AND sem.sem_is_active = 1;");
	$sy_ret=mysqli_fetch_array($sy_query); 
  
	//check if user logged in is already have application
  if(is_null($ret['app_id'])){
  	$href = "AdmissionForm.php";
  } else {
    $href = "AdmissionForm2.php";
		if ($ret['app_status'] == 2) {
			echo '<style type="text/css">
		            #transNav{
		              display: block !important;
		            }
           	</style>';
		}
  }

  //check if user logged in is already admitted
  if(!is_null($ret['student_num'])){
    echo '<style type="text/css">
		          #transNav{
		            display: none !important;
		          }
		          .nav2{
		          	display: block !important;
		          }
          </style>';
  }
?>

<!-- menu toggle -->
<input type="checkbox" id = "nav-toggle" name="">

<!-- menu navigation bar -->
<div class="sidebar">
	<div class = "sidebar-bars">
		<h4>
			<label for="nav-toggle">
				<span id = "bars" class="las la-bars"></span>
			</label>
			<span id = "menulbl">Menu</span>
		</h4>
	</div>
	<div class="sidebar-menu">
		<ul>
			<li>
				<a href="StudentDashboard.php" class="<?php echo($dstyle)?>">
					<span class="las la-home" ></span>
					<span>Dashboard</span>
				</a>
			</li>
			<li>
				<a href="<?php echo($href)?>" class="<?php echo($afstyle)?>">
					<span class="las la-file-alt"></span>
					<span>Admission Form</span>
				</a>
			</li>
			<li>
				<a href="Transaction.php" id = "transNav" class="<?php echo($tstyle)?>" style="display: none;">
					<span class="las la-credit-card"></span>
					<span>Payment Transaction</span>
				</a>
			</li>
			<li>
				<a href="RequestDocument.php" class="nav2 <?php echo($rdstyle)?>" style="display: none;">
					<span class="las la-print"></span>
					<span>Request Documents</span>
				</a>
			</li>
			<li>
				<a href="ViewSchedules.php" class="nav2 <?php echo($vsstyle)?>" style="display: none;">
					<span class="las la-calendar"></span>
					<span>View Schedules</span>
				</a>
			</li>
			<li>
				<a href="ViewAccountCard.php" class="nav2 <?php echo($vastyle)?>" style="display: none;">
					<span class="las la-file-invoice"></span>
					<span>View Account Card</span>
				</a>
			</li>
			<li>
				<a href="ViewGrades.php"  class="nav2 <?php echo($grdstyle)?>" style="display: none;">
					<span class="las la-pen-nib"></span>
					<span>Grades</span></a>
			</li>
			<li>
				<a href="EnrolledTransaction.php" class="nav2 <?php echo($etstyle)?>" style="display: none;">
					<span class="las la-credit-card"></span>
					<span>Transactions</span>
				</a>
			</li>
		</ul>
	</div>
</div>

<!-- top navigation bar-->
<div class="main-content">
	<header>
		<h2>
			<label for="nav-toggle" style="display: none;">
				<span id = "bars" class="las la-bars"></span>
			</label>
			<span><img src="../../images/actsicon.png" width="35px" height="35px" alt=""></span>
			ACTS Web Portal
		</h2>

		<!-- user setting navigation -->
		<div class="user-wrapper">
			<ul>
				<li>
					<a href="#">
						<p id="user" style="font-size: 16px;"><?php echo $ret['username'] ?><br><span>Student</span></p>
						<?php echo '<img src="data:image;base64,'.$ret['image'].'" onerror=this.src="../../images/user-icon.png">'; ?>
						<i class="las la-angle-down"></i>
					</a>
					<div class="user-drop">
						<ul>
							<li>
								<a href="StudentProfile.php">
									<i class="las la-user-edit"></i><p>Profile</p><span>></span>
								</a>
							</li>
							<li>
								<a href="ChangePassword.php?type=<?php echo $ret['account_type'] ?>">
									<i class="las la-key"></i><p>Change Password</p><span>></span>
								</a>
							</li>
							<!-- <li>
								<a href="">
									<i class="las la-question-circle"></i><p>Help</p><span>></span>
								</a>
							</li> -->
							<li>
								<a onclick="Logout()">
									<i class="las la-sign-out-alt"></i><p>Logout</p><span>></span>
								</a>
							</li>
						</ul>
					</div>
				</li>
			</ul>
		</div>
	</header>
<script type="text/javascript">
	// toggle menu, make active
	document.querySelector(".user-wrapper ul li").addEventListener("click",function(){
		this.classList.toggle("active")
	});

	//logout function
	function Logout() {
  	if (confirm("Are you sure to logout?") == true) {
  		document.location = "../../logout.php";
  	} else {
  		//do nothing
  	}
	}
</script>


