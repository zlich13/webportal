<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  require 'PHPMailer-master/src/Exception.php';
  require 'PHPMailer-master/src/PHPMailer.php';
  require 'PHPMailer-master/src/SMTP.php';

function send_mail($recipient,$subject,$message)
{

  $mail = new PHPMailer();
  $mail->IsSMTP();

  $mail->SMTPDebug  = 0;  
  $mail->SMTPAuth   = TRUE;
  $mail->SMTPSecure = "tls";
  $mail->Port       = 587;
  $mail->Host       = "smtp.gmail.com";
  //$mail->Host       = "smtp.mail.yahoo.com";
  $mail->Username   = "actstemporary@gmail.com";//change this to ACTS email
  $mail->Password   = "mllzkilxtmxujcvk";//change this to ACTS password

  $mail->IsHTML(true);
  $mail->AddAddress($recipient, "User");
  $mail->SetFrom("actstemporary@gmail.com", "ACTS Web Portal");//change this to ACTS email
  //$mail->AddReplyTo("reply-to-email", "reply-to-name");
  //$mail->AddCC("cc-recipient-email", "cc-recipient-name");
  $mail->Subject = $subject;
  $content = $message;

  $mail->MsgHTML($content); 
  if(!$mail->Send()) {
    echo "<script>alert('Error! try again later.');  </script>";
    //echo "<pre>";
    //var_dump($mail);
    return false;
  } else {
    echo "<script>alert('Code sent to your email! Also check your spam folder if not found.');  </script>";
    return true;
  }

}

?>