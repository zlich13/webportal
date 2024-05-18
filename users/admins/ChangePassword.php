<?php
session_start();
error_reporting(0);

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
    <title>ACTS Web Portal | Change Password</title>
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- page css -->
    <link rel="stylesheet" href="../../css/navigation.css"/>
    <link rel="stylesheet" href="../../css/style.css"/>
    <!--icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
    <!-- lineawesome icons -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
</head>
<body>

<?php 
    $type = $_GET['type'];

    //include navigationbar
    if (is_null($type)) {
        include('NavigationBar.php'); 
    } else {
        include('../students/NavigationBar.php'); 
    }
   
    //change password validation
    if(isset($_POST['change'])){
        $opass = trim($_POST['opassword']);
        $npass = trim($_POST['npassword']);
        $cpass = trim($_POST['cpassword']);
        if (!password_verify($opass, $ret['password'])) {
            //check if current password is incorrect
            echo "<script>alert('Current password incorrect!');</script>";     
        } else {
            //check if new password is atleast 8 characters long
            if (strlen($npass) < 8){
                echo "<script>alert('New Password must be atleast 8 characters.');  </script>";
            } else {
                //check if new password = confirm password
                if ($npass != $cpass) {
                    echo "<script>alert('Passwords does not match!');  </script>";
                } else {
                    try {
                        // Hash the new password
                        $hash_pass = password_hash($npass, PASSWORD_DEFAULT);
                        $stmt = $con->prepare("UPDATE user_accounts SET password = ? WHERE id = ?");
                        $stmt->bind_param("si", $hash_pass, $uid);
                        if ($stmt->execute()) {
                            echo "<script>alert('Password changed successfully! Use new password on next login.');</script>";
                        } else {
                            echo "<script>alert('Error! try again later.');</script>";
                        }
                        // Close the statement
                        $stmt->close();
                    } catch (Exception $e) {
                        // Display an error message if an exception is caught
                        echo "<script>alert('Error! " . $e->getMessage() . "');</script>";
                    }

                }
            }     
        }    
    }
?>
    <main>
        <div class="box">
        <form class="change" method="post">
            <div class="input-container">
                <label for="opassword">Current Password</label>
                <input id="opassword" name="opassword" type="password" required="">
                <span id="eye" onclick="hide('opassword','eye1','eyeslash1')">
                    <i id = "eye1" class="las la-eye"></i>
                    <i id = "eyeslash1" class="las la-eye-slash"></i>
                </span>
            </div>
            <div class="input-container">
                <label for="npassword">New Password</label>
                <input id="npassword" name="npassword" type="password" placeholder="Must be atleast 8 characters" required="">
                <span id="eye" onclick="hide('npassword','eye2','eyeslash2')">
                    <i id = "eye2" class="las la-eye"></i>
                    <i id = "eyeslash2" class="las la-eye-slash"></i>
                </span>
            </div>
            <div class="input-container">
                <label for="cpassword">Confirm New Password</label>
                <input id="cpassword" name="cpassword" type="password" required="">
                <span id="eye" onclick="hide('cpassword','eye3','eyeslash3')">
                    <i id = "eye3" class="las la-eye"></i>
                    <i id = "eyeslash3" class="las la-eye-slash"></i>
                </span>
            </div>  
            <br>
            <button class="btn btn-success" type="submit" name="change">Change</button>
        </form> 
        </div>
    </main>

    <script type="text/javascript">
        //show or hide passwords, toggle eye icons
        function hide(input, eye, eyeslash){
            var x = document.getElementById(input);
            var y = document.getElementById(eye);
            var z = document.getElementById(eyeslash);

            if(x.type === 'password'){
                x.type = "text";
                y.style.display = "inline-block";
                z.style.display = "none";
            } else {
                x.type = "password";
                y.style.display = "none";
                z.style.display = "inline-block";
            }
        }
    </script>

<style>

#eye1, #eye2, #eye3{
    display: none;
}

#eye{
    font-size: 14px;
    position: absolute;
    cursor: pointer;
    margin-top: -35px;
    margin-left: 300px;
    color: grey;
}

.input-container label{
    color: var(--main-color);
}

input::placeholder{
    font-size: 12px;
}

.change{
    width: 328px;
}

.change input{
    display: block;
    width: 100%;
    box-sizing: border-box;
    border-radius: 5px;
    border: 1px solid #aaa;
    padding: 7px;
    margin-bottom: 10px;
    font-size: 0.875rem;
    outline: none;
}

.btn{
    width: 100%;
}

.box{
    display: flex;
}
</style>

</body>

</html>
