<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    // Get form data
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
    
    // Get the file data
    $file = $_FILES['captureFace']['tmp_name'];
    $originalFileName = $_FILES['captureFace']['name'];
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    
    // Rename file with combination of name and dob
    $newFileName = $fname . "_" . $lname . "_" . $dob . "." . $fileExtension;
    $uploadDir = 'registered_candidate/';
    $uploadFile = $uploadDir . basename($newFileName);

    // Save file to server folder
    if (move_uploaded_file($file, $uploadFile)) {
        // Insert data into the database
        $sql = "INSERT INTO candidates (fname, lname, phone, email, gender, dob, education, experience, industry, address, pin, captureFace)
                VALUES ('$fname', '$lname', '$phone', '$email', '$gender', '$dob', '$education', '$experience', '$industry', '$address', '$pin', '$newFileName')";

        if ($conn->query($sql) === TRUE) {
            // Send email
            $to = "raviraj.0897@gmail.com";
            $subject = "Candidate Registration Form Submission";
            $message = "
            <html>
            <head>
            <title>Candidate Registration Form Submission</title>
            </head>
            <body>
            <p>Thank you for submitting your details. Here is a copy of your submission:</p>
            <table>
            <tr><th>First Name</th><td>$fname</td></tr>
            <tr><th>Last Name</th><td>$lname</td></tr>
            <tr><th>Phone</th><td>$phone</td></tr>
            <tr><th>Email</th><td>$email</td></tr>
            <tr><th>Gender</th><td>$gender</td></tr>
            <tr><th>Date of Birth</th><td>$dob</td></tr>
            <tr><th>Education</th><td>$education</td></tr>
            <tr><th>Experience</th><td>$experience</td></tr>
            <tr><th>Industry</th><td>$industry</td></tr>
            <tr><th>Address</th><td>$address</td></tr>
            <tr><th>Pin Code</th><td>$pin</td></tr>
            </table>
            </body>
            </html>
            ";

            // To send HTML mail, the Content-type header must be set
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            $headers .= 'From: <info@retailcareer.org>' . "\r\n";
            
            // Attachment
            $fileContentEncoded = chunk_split(base64_encode(file_get_contents($uploadFile)));
            $boundary = md5("random"); // define boundary with a md5 hashed value
            $headers .= "MIME-Version: 1.0\r\n"; 
            $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n"; 

            $body = "--$boundary\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body .= $message . "\r\n\r\n";
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/octet-stream; name=\"" . $newFileName . "\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"" . $newFileName . "\"\r\n\r\n";
            $body .= $fileContentEncoded . "\r\n\r\n";
            $body .= "--$boundary--";

            if (mail($to, $subject, $body, $headers)) {
                echo json_encode(["message" => "Your application is submitted successfully."]);
            } else {
                echo json_encode(["error" => "Data saved but email sending failed."]);
            }
        } else {
            echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Failed to upload file."]);
    }

    $conn->close();
}
?>