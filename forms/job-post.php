<?php
$servername = getenv('monorail.proxy.rlwy.net');
$username = getenv('root');
$password = getenv('ravi@3308');
$dbname = getenv('railway');
$port = getenv('31028');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert data
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $posted_date = $_POST['posted_date'];
    $apply_url = $_POST['apply_url'];

    $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, description, posted_date, apply_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $company, $location, $description, $posted_date, $apply_url);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Job inserted successfully"]);
    } else {
        echo json_encode(["error" => "Error inserting job: " . $stmt->error]);
    }
    $stmt->close();
} else {
    // Fetch the first 6 jobs
    $sql = "SELECT * FROM jobs LIMIT 6";
    $result = $conn->query($sql);

    $jobs = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
    }
    echo json_encode($jobs);
}

$conn->close();

// Return data as JSON
header('Content-Type: application/json');
?>
