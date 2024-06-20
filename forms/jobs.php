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

// Fetch the first 6 jobs
$sql = "SELECT * FROM jobs LIMIT 6";
$result = $conn->query($sql);

$jobs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($jobs);
?>
