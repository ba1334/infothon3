<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

include('includes/config.php'); // Ensure this includes the database connection setup

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $fromDate = $_POST['fromdate'];
    $toDate = $_POST['todate'];
    $message = $_POST['message'];
    
    // Ensure the user is logged in and the session contains their email
    if (!isset($_SESSION['login']) || empty($_SESSION['login'])) {
        die("User not logged in. Please log in to book.");
    }

    $useremail = $_SESSION['login'];
    $vehicleId = 1; // Replace with the correct Vehicle ID you are using

    // Validate form data
    if (empty($fromDate) || empty($toDate) || empty($message)) {
        die("Please fill all fields.");
    }
    
    // Ensure the database connection is established
    if (!isset($dbh)) {
        die("Database connection error.");
    }

    // Save booking details to database
    $stmt = $dbh->prepare("INSERT INTO tblbooking (VehicleId, userEmail, FromDate, ToDate, message) VALUES (:vehicleId, :useremail, :fromDate, :toDate, :message)");
    $stmt->bindParam(':vehicleId', $vehicleId);
    $stmt->bindParam(':useremail', $useremail);
    $stmt->bindParam(':fromDate', $fromDate);
    $stmt->bindParam(':toDate', $toDate);
    $stmt->bindParam(':message', $message);
    
    if ($stmt->execute()) {
        // Send email confirmation
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = ''; // Your email
            $mail->Password = '';  // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your_email@gmail.com', 'Car Rental');
            $mail->addAddress($useremail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Booking Confirmation';
            $mail->Body    = 'Your booking from ' . $fromDate . ' to ' . $toDate . ' has been confirmed.';
            


            $mail->send();
            echo 'You Booking has confirmed and an email has been sent.';
            header('Location: my-booking.php');
            exit();
           
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $stmt->errorInfo()[2];
    }
}
?>
