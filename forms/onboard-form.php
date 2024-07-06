<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "career-retail";
$password = "Retail.June@2024";
$dbname = "retail_career";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle file upload and renaming
function handleFileUpload($fileInputName, $name) {
    $timestamp = time();
    $targetDir = "onboard/";
    $originalFileName = basename($_FILES[$fileInputName]["name"]);
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $newFileName = $name . "_" . $fileInputName . "_" . $timestamp . "." . $fileExtension;
    $targetFilePath = $targetDir . $newFileName;

    if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
        return $targetFilePath;
    } else {
        return null;
    }
}

// Function to send email with attachments
function sendEmail($to, $subject, $body, $attachments = []) {
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    require 'PHPMailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.retailcareer.org'; // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'contact@retailcareer.org'; // Your SMTP username
        $mail->Password = 'Retail@2024'; // Your SMTP password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('contact@retailcareer.org', 'Retail Career');
        $mail->addAddress($to);

        // Attachments
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO onboard (name, email, phone, dob, address, education, typeof, position, trade, resume, certificate, transcript, pan, aadhar, photo, declaration, policy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssss", $name, $email, $phone, $dob, $address, $education, $typeof, $position, $trade, $resume, $certificate, $transcript, $pan, $aadhar, $photo, $declaration, $policy);

    // Get the form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $education = $_POST['education'];
    $typeof = $_POST['typeof'];
    $position = $_POST['position'];
    $trade = $_POST['trade'];
    $resume = handleFileUpload('resume', $name);
    $certificate = handleFileUpload('certificate', $name);
    $transcript = handleFileUpload('transcript', $name);
    $pan = handleFileUpload('pan', $name);
    $aadhar = handleFileUpload('aadhar', $name);
    $photo = handleFileUpload('photo', $name);
    $declaration = isset($_POST['declaration']) ? 1 : 0;
    $policy = isset($_POST['policy']) ? 1 : 0;

    // Execute the query
    if ($stmt->execute()) {
        // Send email to admin
        $adminEmail = 'raviraj.0897@gmail.com';
        $subject = 'New On-Board Application from' . ' '  . $name;
        $body = "<p>A new On-Board application has been submitted. Please find the details and attachments.</p>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Phone:</strong> $phone</p>
                    <p><strong>Date of Birth:</strong> $dob</p>
                    <p><strong>Education:</strong> $education</p>
                    <p><strong>Type of Employment:</strong> $typeof</p>
                    <p><strong>Positions:</strong> $position</p>
                    <p><strong>Trade:</strong> $trade</p>
                    <p><strong>Address:</strong> $address</p>
                ";
       
        $attachments = array_filter([$resume, $certificate, $transcript, $pan, $aadhar, $photo]);
        $adminEmailStatus = sendEmail($adminEmail, $subject, $body, $attachments);

         // Send thank you email to user
         $userSubject = "Thank You for Your Application";
         $userBody = "Dear $name,<br><br>
         <p>Congratulations on your successful selection and we appreciate you submitting your details for onboarding.</p>
         <p>Your information will now be undergoing verification for a smooth onboarding experience. If everything is in order, you can expect to receive an offer letter or joining letter directly from the company via your registered email address within a week.</p>
         <p>Should you not receive any communication within that timeframe, please don't hesitate to contact our Human Resources team at helpdesk@retailcareer.org. They'll be happy to assist you further.</p>
         <p>We wish you a successful and fulfilling career and are here to support your professional journey every step of the way. </p>
         <p>This is an automated email, so please don't reply directly. However, if you have any questions, feel free to reach out to us at helpdesk@retailcareer.org</p>
         <br><br>Sincerely,<br><br>Centralized Management Team<br>Retail Career<br><br>www.retailcareer.org";

         $userEmailStatus = sendEmail($email, $userSubject, $userBody);
 
        if ($adminEmailStatus && $userEmailStatus) {
            echo json_encode(["message" => "Application submitted successfully."]);
        } else {
            echo json_encode(["message" => "Application submitted successfully. Failed to send email to the admin."]);
        }
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
