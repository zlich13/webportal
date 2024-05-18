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
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<title>ACTS Web Portal | Profile</title>
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
        //include navigationbar
    	include('NavigationBar.php');

        // uploading image
        if (isset($_POST['upload'])) {
            $tmpName = $_FILES['input-file']['tmp_name'];
            $image = base64_encode(file_get_contents(addslashes($tmpName)));
            // Update the image in the database
            $sql = "UPDATE user_accounts SET image=? WHERE id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", $image, $uid);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script type='text/javascript'> document.location ='StudentProfile.php'; </script>";
            } else {
                echo "<script>alert('Error uploading the image.');</script>";
            }
            mysqli_stmt_close($stmt);
        }

        if (isset($_POST['save'])) {
            //username and email validation
            $new_username = $_POST['username'];
            $new_email = $_POST['email'];
            if($ret['username'] != $new_username){
                $stmt = $con->prepare("SELECT COUNT(*) FROM user_accounts WHERE username = ?");
                $stmt->bind_param("s", $new_username);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($row['COUNT(*)'] > 0) {
                    echo "<script>alert('Username already taken.');</script>";
                } else {
                    $sql = "UPDATE user_accounts set username = ? WHERE id = ?";
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $new_username, $uid);
                    if(!mysqli_stmt_execute($stmt)){
                        echo "<script>alert('Error! try again later.');</script>";
                    }
                }
                mysqli_stmt_close($stmt);
            } 

            if ($ret['email'] != $new_email) {
                $stmt = $con->prepare("SELECT COUNT(*) FROM user_accounts WHERE email = ?");
                $stmt->bind_param("s", $new_email);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($row['COUNT(*)'] > 0) {
                    echo "<script>alert('Email already in use.');</script>";
                } else {
                    $sql = "UPDATE user_accounts set email = ? WHERE id = ?";
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $new_email, $uid);
                    if(!mysqli_stmt_execute($stmt)){
                        echo "<script>alert('Error! try again later.');</script>";
                    }
                }
                mysqli_stmt_close($stmt);
            }
            echo "<script type='text/javascript'> document.location ='StudentProfile.php'; </script>";
        }
    	?>
        <main>
    		<div class = "container">
                <div>
                    <h5 class="title" id="main-title">Profile:</h5>  
                </div>
                <?php if (!is_null($ret['student_num'])) { ?>
                    <div>
                        <form method="post" enctype="multipart/form-data">
                            <div class="section">
                                <div class="user-image">
                                    <?php echo '<img id = "img-preview-file-input" src="data:image;base64,'.$ret['image'].'" onerror=this.src="../../images/user-icon.png" style="width: 200px; height: 200px;  object-fit: contain">'; ?>
                                </div>
                                <div class="user-info">
                                    <h3><strong><?php echo $ret['fname']. ($ret['mname'] ? ' '
                                        . strtoupper(substr($ret['mname'],0,1))
                                        .'. ' : ' ') .$ret['lname']; ?></strong></h3>
                                        <p><?php echo $ret['student_num'] ?></p>
                                    <p><?php echo $ret['name']." (".$ret['acronym'].")" ?></p>
                                    <p><?php echo $ret['student_year']." - ". $ret['sec_name'] ?></p>
                                </div>
                            </div>
                            <div class="section2">
                                <span><input id = "input-file" class = "file" type="file" name="input-file" onchange="previewImage(event)" required></span>
                                <span><button class="btn btn-success" type="submit" name="upload">Upload</button></span>
                            </div>
                        </form>
                    </div>
                <?php } ?>
                <hr>
                <form method="post">
                    <div>
                        <div class="section">
                            <div class="input-fields">
                                <label for="username">Username : </label>
                                <input type="text" name="username" value="<?php echo $ret['username'] ?>" required>
                                <label for="email">Email : </label>
                                <input type="email" name="email" value="<?php echo $ret['email'] ?>" required>
                            </div>
                        </div>
                        <hr>
                        <span class="save"><button class="btn btn-success" type="submit" name="save">Save Changes</button></span>
                    </div>
                </form>
            </div>
    	</main>
        <!-- Preview the image selected by user in input element. -->
        <script src="../../js/preview_image.js"></script>
    </body>
</html>
<style type="text/css">
.input-fields{
    width: 50%;
}
.section{
    display: flex;
}
.user-image span, .labels, .section2{
    display: flex;
    flex-direction: column;
}    
input[type="file"]{
    margin-bottom: 5px;
}
.user-info{
    margin: 0 50px;
}
.user-info h3, p{
    color: var(--main-color);
    margin: 10px 0;
}
.labels label, h5{
    margin: 10px 20px;
    color: var(--main-color);
}
.save{
    display: block;
    text-align: right;
}
.user-info{
    padding: 3rem;
}

@media screen and (max-width: 700px) {
     *{
      font-size: 12px;
    }
    .user-info{
        padding: 10px 0 !important;
    }
    .input-fields{
        width: 100% !important;
    }
    .user-image{
        display: grid;
        place-items: center;
    }
    #img-preview-file-input{
        width: 100px  !important;
        height: 100px !important;
    }
    .section{
        display: block;
    }
    .user-info{
        padding: 0;
        margin: 0;
    }
    .user-info h3, .user-info p{
        margin: 0;
    }
    .btn{
        width: 100%;
    }
}
</style>