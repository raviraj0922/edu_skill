<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
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

// Log POST data for debugging
file_put_contents('php://stderr', print_r($_POST, true));

// Get form data
$name = isset($_POST['name']) ? $_POST['name'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : null;
$note = isset($_POST['note']) ? $_POST['note'] : null;

// Validate form data
if (empty($name) || empty($email) || empty($mobile) || empty($category) || empty($note)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// Insert data into database
$sql = "INSERT INTO join_us (name, email, mobile, category, note)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssss", $name, $email, $mobile, $category, $note);
    if ($stmt->execute()) {
        // Send email to admin
        $to = "raviraj.0897@gmail.com"; // Replace with admin email
        $subject = "New Contact Form Submission";
        $message = "You have received a new contact form submission:\n\n";
        $message .= "Name: $name\n";
        $message .= "Email: $email\n";
        $message .= "Mobile: $mobile\n";
        $message .= "Category: $category\n";
        $message .= "Note: $note\n";

        // Headers
        $headers = "From: contact@retailcareer.org"; // Replace with your sender email

        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send email"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to execute statement: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Failed to prepare statement: " . $conn->error]);
}

$conn->close();
?>
