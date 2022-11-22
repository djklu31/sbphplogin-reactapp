<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Method: GET');

$emailAddresses = $_GET['emailAddresses'];
$hostName = $_GET['hostName'];
$emailTitle = $_GET['emailTitle'];
$sessionID = $_GET['sessionID'];

use PHPMailer\PHPMailer\PHPMailer;

require './PHPMailer/Exception.php';
require './PHPMailer/PHPMailer.php';
require './PHPMailer/SMTP.php';

$mail = new PHPMailer();
$mail->IsSMTP();
// $mail->SMTPDebug = 2;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Host = "smtp.gmail.com";
$mail->Port = 465;
$mail->IsHTML(true);
$mail->Username = "streambox.mail2@gmail.com";
$mail->Password = "bnsxainzymdmlnmy";
$emailsArray = explode(",", $emailAddresses);
foreach ($emailsArray as $email) {
    $mail->AddAddress($email);
}

$mail->Subject = "Session Invitation from " . $hostName;
$mail->Body    = "Session Name:  $emailTitle
<br>
<br>
Join Session: 
https://liveus.streambox.com/ls/launchsession.php?sessionId=$sessionID
<br>
<br>
Streambox Media Players:
To receive this video stream you will need a Streambox Media Player/Decoder. You can download instructions for various Streambox Players/Decoders here:
https://streambox-mediaplayer.s3.us-west-2.amazonaws.com/latest/streambox_mediaplayer_sessions.pdf 
<br>
<br>
If you have any questions, please feel free to contact Streambox at:
Email: support@streambox.com
Phone: +1 206.956.0544, Option 2";
$text = 'Text version of email';
$html = '<html><body>HTML version of email</body></html>';
$crlf = "\n";
$hdrs = array(
    'email' => 'djklu31@gmail.com',
    'Subject' => 'Test subject message',
    'comment' => 'Test comment',
);
if ($mail->send($hdrs))
//if (mail($subject,$message, $headers))
{
    echo "Emails sent successfully to: " . $emailAddresses;
} else {
    echo "Mailed Error: " . $mail->ErrorInfo;
}
