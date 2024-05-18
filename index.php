<?php 
session_start();
session_destroy();
session_start();
error_reporting(0);

//connect to database
include('dbconnection.php');

//login
if(isset($_POST['login'])) {
    $sign_user = $_POST['sign_user'];
    $sign_pass = $_POST['sign_pass'];

    // Prepare the SQL statement
    $stmt = $con->prepare("SELECT id, password, account_type FROM user_accounts WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $sign_user, $sign_user);
    $stmt->execute();
    $ret = $stmt->get_result();

    // Check if there is a matching user
    if ($ret->num_rows <= 0) {
        echo "<script>alert('Invalid Credentials!');</script>";
    } else {
        // Verify the password
        $row = $ret->fetch_assoc();
        if (!password_verify($sign_pass, $row['password'])) {
            echo "<script>alert('Invalid Credentials!');</script>";
        } else {
            // Set the user ID and redirect to the appropriate dashboard
            $_SESSION['uid'] = $row['id'];
            $type = $row['account_type'];
            if ($type == 1) {
                echo "<script type='text/javascript'> document.location ='users/students/StudentDashboard.php'; </script>";
            } else if ($type > 1) {
                echo "<script type='text/javascript'> document.location ='users/admins/AdminsDashboard.php'; </script>";
            }
        }
    }
    $stmt->close();
}

// register
if(isset($_POST['register'])){
    $reg_user = $_POST['reg_user'];
    $reg_email = $_POST['email'];
    $reg_pass = $_POST['reg_pass'];
    $cpassword = $_POST['cpassword'];

    // Check if username or email already exists
    $stmt = $con->prepare("SELECT id FROM user_accounts WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $reg_user, $reg_email);
    $stmt->execute();
    $ret = $stmt->get_result();

    if($ret->num_rows > 0){
        echo "<script>alert('Username or email already in use.');</script>";
    } else {
        if (strlen($reg_pass) < 8){
            echo "<script>alert('Password must be atleast 8 characters.');</script>";
        } else {
            if ($reg_pass != $cpassword) {
                echo "<script>alert('Passwords does not match!');  </script>";
            } else {
                $hash_pass = password_hash($reg_pass, PASSWORD_DEFAULT);
                $code = rand(10000,99999);
                $expires = (time()+(60*4));
                $subject = "ACTS Web Portal Email Verification";
                $stmt = $con->prepare("INSERT INTO email_verify (code, expires, email) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE code = ?, expires = ?");
                $stmt->bind_param("iisii", $code, $expires, $reg_email, $code, $expires);
                if ($stmt->execute()) {
                    sendCode($subject,$code,$reg_email);
                    $verify_user = $reg_user;
                    $verify_email = $reg_email;
                    $verify_pass = $hash_pass;
                    displayVerifyForm();
                } else {
                    echo "<script>alert('Error registering! try again later.');</script>";
                }
            }
        }
    }  
    $stmt->close();
}

function displayVerifyForm(){
    echo '<style>
            #verify_div{
                display: block !important;
            }
            #reg_form{
                display: none;
            }
          </style>';
}

// verify email
if (isset($_POST['verify'])) {
    $verify_code=$_POST['verify_code'];
    $insert_email=$_POST['verify_email'];
    $insert_pass=$_POST['verify_pass'];
    $insert_user=$_POST['verify_user'];
    $stmt = $con->prepare("SELECT * FROM email_verify WHERE code = ? AND email = ? LIMIT 1");
    $stmt->bind_param("is", $verify_code, $insert_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $ret = $result->fetch_assoc();
    if($ret){
        //check if code is expired
        $time=time();
        if ($ret['expires'] > $time) {
            $stmt = $con->prepare("INSERT INTO user_accounts (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $insert_user, $insert_email, $insert_pass);
            if($stmt->execute()){
                echo "<script>alert('Registered successfully.');  </script>";
                echo "<script type='text/javascript'>document.location ='index.php';</script>";
            } else {
                echo "<script>alert('Error registering! try again later.');  </script>";
            }
        }else{
            echo "<script>alert('Code expired! Resend code below.');</script>";
            $verify_user = $insert_user;
            $verify_email = $insert_email;
            $verify_pass = $insert_pass;
            displayVerifyForm();
        }
    } else{
        echo "<script>alert('Code incorrect!');</script>";
        $verify_user = $insert_user;
        $verify_email = $insert_email;
        $verify_pass = $insert_pass;
        displayVerifyForm();
    }
    $stmt->close();
}

//resend verification email
if (isset($_POST['resend_verify'])) {
    $hidden_verify_email = $_POST['hidden_verify_email'];
    $hidden_user = $_POST['hidden_user'];
    $hidden_pass = $_POST['hidden_pass'];
    $code = rand(10000,99999);
    $expires = (time()+(60*4));
    $subject = "ACTS Web Portal Email Verification";
    //update record in database
    $stmt = $con->prepare("UPDATE email_verify SET code = ?, expires = ? WHERE email = ?");
    $stmt->bind_param("iis", $code, $expires, $hidden_verify_email);
    if($stmt->execute()){
        //send code to email
        sendCode($subject,$code, $hidden_verify_email);
        $verify_user = $hidden_user;
        $verify_email = $hidden_verify_email;
        $verify_pass = $hidden_pass;
        displayVerifyForm();
    } else {
        echo "<script>alert('Error! try again later.');  </script>";
    }
    $stmt->close();
}

// send code for password reset
if (isset($_POST['sendcode'])) {
    //check if email is registered
    $user_email = ($_POST['user_email']);
    $stmt = $con->prepare("SELECT COUNT(*) FROM user_accounts WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['COUNT(*)'] <= 0) {
        echo "<script>alert('Email not registered!');</script>";
        echo "<script type='text/javascript'> document.location ='index.php?action=send_code'; </script>";
        exit;
    }

    $code = rand(10000,99999);
    $expires = (time()+(60*4));
    $subject = "ACTS Web Portal Password Change";
    //check if there is previous record in database
    $stmt = $con->prepare("INSERT INTO forgot_pass (code, expires, email) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE code = ?, expires = ?");
    $stmt->bind_param("iissi", $code, $expires, $user_email, $code, $expires);
    if ($stmt->execute()) {
        sendCode($subject,$code,$user_email);
        $set_email = $user_email;
        displayConfirmForm();
    } else {
        echo "<script>alert('Error! try again later.');</script>";
    }
    $stmt->close();
}

//resend the code
if (isset($_POST['resend'])) {
    $set_email= $_POST['hidden_email'];

    $code = rand(10000,99999);
    $expires = (time()+(60*4));
    $subject = "ACTS Web Portal Password Change";
    //update record in database
    $stmt = $con->prepare("UPDATE forgot_pass SET code = ?, expires = ? WHERE email = ?");
    $stmt->bind_param("iis", $code, $expires, $set_email);
    if($stmt->execute()){
        //send code to email
        sendCode($subject,$code, $set_email);
        displayConfirmForm();
    } else {
        echo "<script>alert('Error! try again later.');  </script>";
    }
    $stmt->close();
}

//confirm the code if correct
if (isset($_POST['confirm'])){
    $get_email=$_POST['confirm_email'];
    $get_code=$_POST['code'];

    $stmt = $con->prepare("SELECT * FROM forgot_pass WHERE code = ? AND email = ? LIMIT 1");
    $stmt->bind_param("is", $get_code, $get_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $ret = $result->fetch_assoc();

    if($ret){
        //check if code is expired
        $time=time();
        if ($ret['expires'] > $time) {
            $_SESSION['s_email'] = $get_email;
            echo "<script type='text/javascript'> document.location ='changepassword.php'; </script>";
        }else{
            echo "<script>alert('Code expired! Resend code below.');</script>";
            $set_email=$get_email;
            displayConfirmForm();
        }
    } else{
        echo "<script>alert('Code incorrect!');</script>";
        $set_email=$get_email;
        displayConfirmForm();
    }
    $stmt->close();
}

//function to send the code to users email
function sendCode($subject,$code,$user_email){
    //php mailer
    include('mail.php');
    $message = "Your code is ".$code.". This will expire in 3 minutes.";
    $recipient = $user_email;
    send_mail($recipient,$subject,$message);
}

//function to toggle forms
function displayConfirmForm(){
    echo '<style>
            #confirm_div{
                display: block !important;
            }
            #code_form{
                display: none;
            }
        </style>';
}

//if user leave changepassword.php as previous page, destroy the session
if (isset($_SESSION['previous'])) {
   if (basename($_SERVER['PHP_SELF']) != $_SESSION['previous']) {
        session_destroy();
   }
}

?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, intial-scale=1.0">
        <title>ACTS Web Portal</title>
        <!-- bootstrap css -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <!-- page css -->
        <link rel="stylesheet" href="css/index.css"/>
        <!--icon-->
        <link rel="shortcut icon" href="images/actsicon.png"/>
        <!-- lineawesome icons -->
        <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    </head>
    <body>
        <div class="split-screen">
            <div class="left">
                <section class="copy">
                    <h1>Welcome to ACTS Web Portal</h1>
                    <div>
                    <h3>Senior High</h3>
                    <div class="left-align">
                    <!-- display offered strands -->
                    <?php 
                        $sql=("SELECT name, acronym FROM course_strand WHERE type =1");
                        $result=$con->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<li><strong> (".$row["acronym"].") </strong> - ".$row["name"]."</li>";
                        } 
                    ?>
                    </div>
                    <h3>College</h3>
                    <div class="left-align">
                    <!-- display offered courses -->
                    <?php 
                        $sql=("SELECT name, acronym FROM course_strand WHERE type =2");
                        $result=$con->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<li><strong> (".$row["acronym"].") </strong> - ".$row["name"]."</li>";
                        } 
                    ?>
                     </div>
                </section>
            </div>
            <div class="right">
                <div class="box">
                    <!-- toggle forms based on action -->
                    <?php
                        $action = $_GET['action'];
                        if ($action==null) { ?>
                            <form method="post">
                                <section class="copy">
                                <img src="images/actsicon.png" width="320px" />
                                <h2>Sign In</h2>
                                </section>
                                <div class="input-container">
                                    <label for="sign_user"><b>Username or Email</b></label>
                                    <input id="sign_user" name="sign_user" type="text" required>
                                </div>   
                                <div class="input-container">
                                    <label for="sign_pass"><b>Password</b></label>
                                    <input id="sign_pass" name="sign_pass" type="password" required>
                                    <span id="eye" onclick="hide('sign_pass','eye1','eyeslash1')">
                                        <i id = "eye1" class="las la-eye"></i>
                                        <i id = "eyeslash1" class="las la-eye-slash"></i>
                                    </span>
                                </div>
                                <a href="index.php?action=send_code" class="right_link">
                                    <p><small>Forgot password?</small></p>
                                </a>
                                <button class = "btn" type="submit" name="login">Sign In</button>
                                <div class="center"><small>Doesn't have an account yet?
                                    <a href="index.php?action=register">
                                        <strong>Register here</strong> 
                                    </a></small>
                                </div>
                            </form> <?php 
                        } else if ($action=='send_code') { ?>
                            <div>
                                <section class="copy">
                                    <img src="images/actsicon.png" width="320px" />
                                    <h2>Reset Password</h2>
                                </section>
                                <form id="code_form" method="post">
                                    <div class="input-container">
                                        <label for="user_email"><b>Enter registered email</b></label>
                                        <input id="user_email" name="user_email" type="email" required>
                                    </div>
                                    <div class="rec-btns">
                                        <button class="btn" type="submit" name="sendcode">Send Code</button>
                                    </div>
                                </form>
                                <div id="confirm_div" style="display: none;">
                                    <form id= "confirm_form" method = "post" >
                                        <label><b>Enter the code sent to your email.</b></label>
                                        <input id="confirm_email" name="confirm_email" type="email" value = "<?php echo $set_email ?>" readonly>
                                        <div class="input-container">
                                            <input id="code" name="code" type="number" placeholder="Code" required>
                                        </div>                            
                                        <div class="rec-btns">
                                            <button class="btn" type="submit" name="confirm">Confirm</button>
                                        </div>
                                    </form>
                                    <form id="resend_form" method="post">
                                        <input name = "hidden_email" type="email" value = "<?php echo $set_email ?>" readonly style="display: none;">
                                        <div class="center">
                                            <p><small>No code received? 
                                                <button type="submit" name= "resend" class="button_link"><strong> Resend code </strong>
                                                </button>
                                            </small></p>
                                        </div>
                                    </form>
                                </div>
                                <div class="center">
                                    <a href="index.php">
                                        <small> Cancel </small>
                                    </a>
                                </div>
                            </div> <?php 
                        } else if ($action=='register') { ?>
                            <div>
                                <section class="copy">
                                    <img src="images/actsicon.png" width="320px" />
                                    <h2>Student's Registration</h2>
                                </section>
                                <form id="reg_form" method="post">
                                <div class="input-container">
                                    <label for="reg_user"><b>Username</b></label>
                                    <input id="reg_user" name="reg_user" type="text" required="">
                                </div>   
                                <div class="input-container">
                                    <label for="email"><b>Email</b></label>
                                    <input id="email" name="email" type="email" required="">
                                </div>
                                <div class="input-container">
                                    <label for="reg_pass"><b>Password</b></label>
                                    <input id="reg_pass" name="reg_pass" type="password" placeholder="Must be atleast 8 characters" required="">
                                    <span id="eye" onclick="hide('reg_pass','eye1','eyeslash1')">
                                        <i id = "eye1" class="las la-eye"></i>
                                        <i id = "eyeslash1" class="las la-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="input-container">
                                    <label for="cpassword"><b>Confirm Password</b></label>
                                    <input id="cpassword" name="cpassword" type="password" required="">
                                    <span id="eye" onclick="hide('cpassword','eye2','eyeslash2')">
                                        <i id = "eye2" class="las la-eye"></i>
                                        <i id = "eyeslash2" class="las la-eye-slash"></i>
                                    </span>
                                </div>  
                                <br>
                                <button class="btn" type="submit" name="register">Register</button>
                                <div class="center"><small>Already have an account?
                                    <a href="index.php">
                                        <strong> Sign In </strong>
                                    </a></small>
                                </div>
                            </form>
                            <div id="verify_div" style="display: none;">
                                    <form method = "post" >
                                        <label><b>Enter code to verify your email.</b></label>
                                        <input id="verify_email" name="verify_email" type="email" value = "<?php echo $verify_email ?>" readonly style="display: none;">
                                        <input id="verify_user" name="verify_user" type="text" value = "<?php echo $verify_user ?>" readonly style="display: none;">
                                        <input id="verify_pass" name="verify_pass" type="password" value = "<?php echo $verify_pass ?>" readonly style="display: none;">
                                        <div class="input-container">
                                            <input id="verify_code" name="verify_code" type="number" placeholder="Code" required>
                                        </div>                            
                                        <div class="rec-btns">
                                            <button class="btn" type="submit" name="verify">Verify</button>
                                        </div>
                                    </form>
                                    <form id="resend_form" method="post">
                                        <input name = "hidden_verify_email" type="email" value = "<?php echo $verify_email ?>" readonly style="display: none;">
                                        <input id="hidden_user" name="hidden_user" type="text" value = "<?php echo $verify_user ?>" readonly style="display: none;">
                                        <input id="hidden_pass" name="hidden_pass" type="password" value = "<?php echo $verify_pass ?>" readonly style="display: none;">
                                        <div class="center">
                                            <p><small>No code received? 
                                                <button type="submit" name= "resend_verify" id="resend_verify" class="button_link"><strong> Resend code </strong>
                                                </button>
                                            </small></p>
                                        </div>
                                    </form>
                                    <div class="center">
                                    <a href="index.php">
                                        <small> Cancel </small>
                                    </a>
                                </div>
                            </div>
                            </div>
                              <?php 
                        } ?> 
                </div>
            </div>
        </div>
        <!-- eye icons js -->
        <script src="js/eye_icons.js"></script>
    </body>
</html>
<!-- hide spin button on input type number -->
<style type="text/css">
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }
</style>