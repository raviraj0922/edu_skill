<?php
$servername = "localhost";
$username = "ranjeet055";
$password = "ranjeetsingh@055";
$dbname = "charu_02";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$limit = intval($_GET['limit']);
$offset = intval($_GET['offset']);
$company = isset($_GET['company']) ? $_GET['company'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$title = isset($_GET['title']) ? $_GET['title'] : '';

// Base query
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM jobs WHERE 1=1";

// Add filters
if (!empty($company)) {
    $query .= " AND company LIKE '%$company%'";
}
if (!empty($location)) {
    $query .= " AND location LIKE '%$location%'";
}
if (!empty($title)) {
    $query .= " AND title LIKE '%$title%'";
}

$query .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// Fetch total number of results
$totalResult = $conn->query("SELECT FOUND_ROWS() as total");
$totalRow = $totalResult->fetch_assoc();
$total = $totalRow['total'];

$jobs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

echo json_encode(['jobs' => $jobs, 'total' => $total]);

$conn->close();
?>
