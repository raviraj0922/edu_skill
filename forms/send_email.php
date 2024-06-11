<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $category = $_POST['category'];
    $note = $_POST['note'];

    $to = "raviraj.0897@gmail.com";
    $subject = "New Contact Form Submission";
    $message = "Name: $name\nEmail: $email\nMobile: $mobile\nCategory: $category\nNote: $note";
    $headers = "From: $email";

    if (mail($to, $subject, $message, $headers)) {
        echo "Message sent successfully";
    } else {
        echo "Error sending message";
    }
} else {
    echo "Invalid request method";
}
?>
