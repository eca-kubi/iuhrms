<?php /** @noinspection DuplicatedCode */

// Require the bootstrap file
require_once __DIR__ . '/bootstrap.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Get email details from command arguments or any other source you prefer
// For simplicity, I'm directly accessing them using $argv but you might want to use a more secure method
$recipient_address = $argv[1];
$subject = $argv[2];
$body = $argv[3];
$password = $argv[4];

// Log the email details
Helpers::log_info("Recipient: $recipient_address");
Helpers::log_info("Subject: $subject");
Helpers::log_info("Body: $body");


$mailer = new PHPMailer(true);
// set password
try {
    // Configure Mailer
    $mailer = new PHPMailer(true);
    $mailer->SMTPDebug = SMTP::DEBUG_CLIENT;
    // send debug output to log file
    $mailer->Debugoutput = function ($str, $level) {
        // Use Helpers::logInfo() to log the debug output to avoid sending multiple headers to the client
        Helpers::log_info($str);
    };
    $mailer->isSMTP();
    $mailer->Host = EMAIL_SMTP_HOST;
    $mailer->SMTPAuth = true;
    $mailer->Username = EMAIL_SENDER_ADDRESS;
    $mailer->Password = $password;
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = EMAIL_SMTP_PORT;
    // Set the sender
    $mailer->setFrom(EMAIL_SENDER_ADDRESS, EMAIL_SENDER_NAME);

    // Recipients
    $mailer->addAddress($recipient_address);                     // Add a recipient

    // Content
    $mailer->isHTML(true);                                       // Set email format to HTML
    $mailer->Subject = $subject;
    $mailer->Body = $body;

    $mailer->send();
    echo "Message has been sent to $recipient_address\n";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mailer->ErrorInfo}\n";
}

