<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Initialize a variable to hold the response
$response = ["message" => "", "error" => ""];

// Check request method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "posted_job/";
    $target_file = $target_dir . basename($_FILES["pdf_link"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a actual PDF or fake PDF
    if ($fileType != "pdf") {
        $response["error"] = "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $response["error"] = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (5MB limit)
    if ($_FILES["pdf_link"]["size"] > 5000000) {
        $response["error"] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Attempt to upload the file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["pdf_link"]["tmp_name"], $target_file)) {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO jobs (category, title, company, location, description, posted_date, apply_url, pdf_link, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $category, $title, $company, $location, $description, $posted_date, $apply_url, $pdf_link, $email, $phone);

            // Set parameters and execute
            $category = $_POST['category']; // <-- Missing semicolon fixed here
            $title = $_POST['title'];
            $company = $_POST['company'];
            $location = $_POST['location'];
            $description = $_POST['description'];
            $posted_date = $_POST['posted_date'];
            $apply_url = isset($_POST['apply_url']) ? $_POST['apply_url'] : 'http://retailcareer.org/registration.html';
            $pdf_link = $target_file;
            $email = isset($_POST['email']) ? $_POST['email'] : 'career@eduskill.org';
            $phone = isset($_POST['phone']) ? $_POST['phone'] : '9654807520';

            if ($stmt->execute()) {
                $response["message"] = "Job inserted successfully";
            } else {
                $response["error"] = "Error inserting job: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response["error"] = "Sorry, there was an error uploading your file.";
        }
    }
} else {
    // Fetch the first 6 jobs
    $sql = "SELECT * FROM jobs LIMIT 6";
    $result = $conn->query($sql);

    $jobs = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
    }
    echo json_encode($jobs);
    exit();
}

$conn->close();
echo json_encode($response);
?>
