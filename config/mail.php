<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  require realpath(dirname(__FILE__) . '/../PHPMailer-master/src/Exception.php');
  require realpath(dirname(__FILE__) . '/../PHPMailer-master/src/PHPMailer.php');
  require realpath(dirname(__FILE__) . '/../PHPMailer-master/src/SMTP.php');

function send_mail($recipient,$subject,$message)
{

  $mail = new PHPMailer();
  //$mail->IsSMTP();

  $mail->SMTPDebug  = true;  
  $mail->SMTPAuth   = TRUE;
  $mail->SMTPSecure = "ssl";
  $mail->Port       = 465;
  $mail->Host       = "smtp.hostinger.com";

  $mail->Username   = "donotreply@thesiscapstonemanager.com";
  $mail->Password   = "Thesis7415@";

  $mail->IsHTML(true);
  $mail->AddAddress($recipient, $subject);
  $mail->SetFrom("donotreply@thesiscapstonemanager.com", "Thesis Capstone Manager");
  $mail->Subject = $subject;
  $content = $message;

  $mail->MsgHTML($content); 
  $mail->Send(); // comment this and enable the code below if having troubles.

  // debugging purposes
  /*if(!$mail->Send()) {
    echo "Error while sending Email.";
    echo "<pre>";
    var_dump($mail);
    return false;
  } else {
    echo "Email sent successfully";
    return true;
  }*/

}

?>