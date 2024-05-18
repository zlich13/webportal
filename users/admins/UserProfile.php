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
                echo "<script type='text/javascript'> document.location ='UserProfile.php'; </script>";
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
            echo "<script type='text/javascript'> document.location ='UserProfile.php'; </script>";
        }
        ?>
        <main>
            <div class = "container">
                <div>
                    <h5 class="title" id="main-title">Profile:</h5>  
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="section">
                        <div class="user-image">
                            <?php echo '<img id="img-preview-file-input" src="data:image;base64,'.$ret['image'].'" onerror=this.src="../../images/user-icon.png" style=" width: auto; height: 200px; object-fit: contain">'; ?>
                        </div>
                    </div>
                    <div class="section2">
                        <span><input class = "file" type="file" name="input-file" id="input-file" onchange="previewImage(event)" required></span>
                         <span><button class="btn btn-success" type="submit" name="upload">Upload</button></span>
                    </div>
                </form>
                <hr>
                <form method="post">
                    <div class="section">
                        <div class="labels">
                            <label>Username : </label>
                            <label>Email : </label>
                        </div>
                        <div class="input-fields">
                            <input type="text" name="username" value="<?php echo $ret['username'] ?>" required>
                            <input type="email" name="email" value="<?php echo $ret['email'] ?>" required>
                        </div>
                    </div>
                    <hr>
                    <span class="save"><button class="btn btn-success" type="submit" name="save">Save Changes</button></span>
                </form>
            </div>
        </main>
        <!-- Preview the image selected by user in input element. -->
         <script src="../../js/preview_image.js"></script>
    </body>
</html>
<style type="text/css">
.section{
    display: flex;
    flex-direction: row;
    align-items: center;
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
</style>