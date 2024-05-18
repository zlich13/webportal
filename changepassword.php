<?php
session_start();
error_reporting(0);

//set this as previous page
$_SESSION['previous'] = basename($_SERVER['PHP_SELF']);

//connect to database
include('dbconnection.php');

//if no email address set, redirect
if (empty($_SESSION['s_email'])) {
    header('location:index.php');
    exit;
} 

$email = $_SESSION['s_email'];

//get the username
$query=mysqli_query($con,"select username from user_accounts where email ='$email'");
$ret=mysqli_fetch_array($query);
if($ret<=0){
    echo "<script>alert('Email not registered!');</script>";
} else {
    $username = $ret['username'];
}

//change password validation
if(isset($_POST['change'])){
    $npass = trim($_POST['npassword']);
    $cpass = trim($_POST['cpassword']);
        if (strlen($npass) < 8){
            echo "<script>alert('New Password must be atleast 8 characters');  </script>";
        } else {
            if ($npass != $cpass) {
                echo "<script>alert('Passwords does not match!');  </script>";
            } else {
                //password hashing
                $hash_pass = password_hash($npass, PASSWORD_DEFAULT);
                $sql = "update user_accounts set password = '$hash_pass' where username ='$username'";
                if($con->query ($sql) === TRUE){
                    //change password and redirect
                    echo "<script>alert('Password changed successfully!');</script>";
                    echo "<script type='text/javascript'> document.location ='index.php'; </script>";
                } else {
                    echo "<script>alert('Error! try again later.');  </script>";
                }
            }
        }
}   
?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, intial-scale=1.0">
        <title>ACTS Web Portal | Reset Password</title>
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
        <div class="right">
            <div>       
                <section class="copy">
                    <img src="images/actsicon.png" width="320px" />
                    <h2>Reset Password</h2>
                </section>
                <form  method="post">
                   <div class="input-container">
                       <label for="username"><b>Username</b></label>
                        <input id="username" name="username" type="text"placeholder="Must be atleast 8 characters" required="" value="<?php echo $username ?>" readonly>
                    </div>
                    <div class="input-container">
                        <label for="npassword"><b>New Password</b></label>
                        <input id="npassword" name="npassword" type="password" placeholder="Must be atleast 8 characters" required="">
                        <span id="eye" onclick="hide('npassword','eye1','eyeslash1')">
                            <i id = "eye1" class="las la-eye"></i>
                            <i id = "eyeslash1" class="las la-eye-slash"></i>
                        </span>
                    </div>
                    <div class="input-container">
                        <label for="cpassword"><b>Confirm New Password</b></label>
                        <input id="cpassword" name="cpassword" type="password" required="">
                        <span id="eye" onclick="hide('cpassword','eye2','eyeslash2')">
                            <i id = "eye2" class="las la-eye"></i>
                            <i id = "eyeslash2" class="las la-eye-slash"></i>
                        </span>
                    </div>  
                    <br>
                    <button class="btn" type="submit" name="change">Change</button>
                </form>
                <div class="center">
                    <a href="index.php"><small> Cancel </small></a>
                </div>
            </div>
        </div>
    </body>
    <!-- eye icons js -->
    <script src="js/index.js"></script>
</html>
<style type="text/css">
 .right{
    margin: auto;
    width: 50%;
 }
</style>