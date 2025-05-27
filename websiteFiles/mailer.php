<?php
// link to the smtp and phpmailer
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

//MailHog SMTP settings
$mail->isSMTP();
$mail->Host = 'localhost';
$mail->SMTPAuth = false;
$mail->Port = 1025;

$mail->isHTML(true); // Ensure HTML emails work

return $mail;
?>
