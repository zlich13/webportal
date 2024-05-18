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

	$uid = $_SESSION['uid'];
	$query=mysqli_query($con,"SELECT * FROM user_accounts where id = $uid");
	$ret=mysqli_fetch_array($query);
	$username = $ret['username'];
	$account_type = $ret['account_type'];

	$info_query=mysqli_query($con,"SELECT * FROM school_info where id = 1");
	$info_ret=mysqli_fetch_array($info_query);

	$sy_query=mysqli_query($con,"SELECT sy.*, sem.* FROM school_years sy JOIN semesters sem ON sy.sy_id=sem.sy_id WHERE sy.is_active = 1 AND sem.sem_is_active = 1;");
	$sy_ret=mysqli_fetch_array($sy_query);
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
						<a href="AdminsDashboard.php" class="<?php echo($dstyle)?>">
						<span class="las la-home" ></span>
						<span>Dashboard</span></a>
					</li>
					<li>
						<a href="Admissions.php?sta=All" class="<?php echo($astyle)?>">
						<span class="las la-file-alt"></span>
						<span>Admission</span></a>
					</li>
					<li>
						<a href="Transactions.php?sta=All" class="<?php echo($tstyle)?>">
						<span class="las la-credit-card"></span>
						<span>Transactions</span></a>
					</li>
					<li>
						<a href="Schedules.php" class="<?php echo($msstyle)?>">
						<span class="las la-calendar-alt"></span>
						<span>Manage Schedules</span></a>
					</li>
					<li>
						<a href="SubjectsManager.php?sta=1" class="<?php echo($mssstyle)?>">
						<span class="las la-book"></span>
						<span>Student Subjects</span></a>
					</li>
					<li>
						<a href="ClassList.php" class="<?php echo($clstyle)?>">
						<span class="las la-clipboard-list"></span>
						<span>Class List</span></a>
					</li>
					<li>
						<a href="DocumentRequests.php?sta=All" class="<?php echo($drstyle)?>">
						<span class="las la-print"></span>
						<span>Document Requests</span></a>
					</li>
					<li>
						<a href="Grades.php" class="<?php echo($grdstyle)?>">
						<span class="las la-pen-nib"></span>
						<span>Grades</span></a>
					</li>
					<?php echo $ret['account_type'] == 3 ? '<li>
					    <a href="DataManagement.php" class="'. $dmstyle .'">
					        <span class="las la-server"></span>
					        <span>Data Management</span>
					    </a>
					</li>' : ''; ?>
					
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

				<div class="user-wrapper">
					<ul>
						<li>
							<a href="#">
								<p style="font-size: 16px;"><?php echo $ret['username'] ?><br> <span><?php echo ($account_type == 3) ? 'Superadmin' : 'Admin'; ?></span></p>
									<?php echo '<img src="data:image;base64,'.$ret['image'].'" onerror=this.src="../../images/user-icon.png">'; ?>
								<i class="las la-angle-down"></i>
							</a>

							<div class="user-drop" id="user-drop">
								<ul>
									<li>
									<a href="UserProfile.php">
										<i class="las la-user-edit"></i>
										<p>Profile</p>
										<span>></span>
									</a>
									</li>           
									<li>
									<a href="ChangePassword.php">
										<i class="las la-key"></i>
										<p>Change Password</p>
										<span>></span>
									</a>
									</li>
									<li>
									</li>
									<li>
										<a onclick="Logout()">
											<i class="las la-sign-out-alt"></i>
											<p>Logout</p>
											<span>></span>
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