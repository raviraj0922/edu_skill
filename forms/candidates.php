<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

    $servername = "localhost";
    $username = "";
    $password = "";
    $dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $education = $_POST['education'];
    $experience = $_POST['experience'];
    $industry = $_POST['industry'];
    $address = $_POST['address'];
    $pin = $_POST['pin'];
    
    // Handle file upload and renaming
    $resume = $_FILES['resume']['name'];
    $resume_tmp = $_FILES['resume']['tmp_name'];
    $timestamp = time();
    $extension = pathinfo($resume, PATHINFO_EXTENSION);
    $new_resume_name = $fname . "_" . $lname . "_" . $timestamp . "." . $extension;
    $resume_folder = 'registered_candidate/' . $new_resume_name;
    
    if (move_uploaded_file($resume_tmp, $resume_folder)) {
        $sql = "INSERT INTO candidates (fname, lname, phone, email, gender, dob, education, experience, industry, address, pin, resume)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssss", $fname, $lname, $phone, $email, $gender, $dob, $education, $experience, $industry, $address, $pin, $resume_folder);

        if ($stmt->execute()) {
            // Prepare email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.retailcareer.org'; // Set the SMTP server to send through
                $mail->SMTPAuth = true;
                $mail->Username = ''; // SMTP username
                $mail->Password = ''; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                //Recipients
                $mail->setFrom('contact@retailcareer.org', 'Job Applications');
                $mail->addAddress('raviraj.0897@gmail.com'); // Add admin email

                // Attachments
                $mail->addAttachment($resume_folder);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'New Job Application from ' . $fname . ' ' . $lname;
                $mail->Body    = "
                    <p>A new job application has been submitted with the following details:</p>
                    <p><strong>First Name:</strong> $fname</p>
                    <p><strong>Last Name:</strong> $lname</p>
                    <p><strong>Phone:</strong> $phone</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Gender:</strong> $gender</p>
                    <p><strong>Date of Birth:</strong> $dob</p>
                    <p><strong>Education:</strong> $education</p>
                    <p><strong>Experience:</strong> $experience</p>
                    <p><strong>Industry:</strong> $industry</p>
                    <p><strong>Address:</strong> $address</p>
                    <p><strong>Pin Code:</strong> $pin</p>
                ";

                $mail->send();
                $response = array("message" => "Application Submitted successfully!");
            } catch (Exception $e) {
                $response = array("error" => "Data inserted, but email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        } else {
            $response = array("error" => "Error: " . $stmt->error);
        }

        $stmt->close();
    } else {
        $response = array("error" => "Error uploading file.");
    }

    $conn->close();

    echo json_encode($response);
}
?>
