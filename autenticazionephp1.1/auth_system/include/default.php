<?php
  session_start();
  require_once "./include/db.php";
  require_once "./include/AuthSys.php";
  require "./lib/PHPMailer-master/src/POP3.php";
  require "./lib/PHPMailer-master/src/SMTP.php";
  require "./lib/PHPMailer-master/src/PHPMailer.php";
  $mail = new PHPMailer\PHPMailer\PHPMailer();  
  $auth = new AuthSys($PDO, $mail);
  if($auth->utenteLoggato()) {
    echo "Sei loggato - <a href='logout.php'>logout</a>";
  }
?>