<?php
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

$limit = intval($_GET['limit']);
$offset = intval($_GET['offset']);
$company = isset($_GET['company']) ? $_GET['company'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Base query
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM jobs WHERE 1=1";

// Parameters array
$params = [];
$types = "";

// Add filters
if (!empty($company)) {
    $query .= " AND company LIKE ?";
    $params[] = "%$company%";
    $types .= "s";
}
if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}
if (!empty($category)) {
    $query .= " AND category LIKE ?";
    $params[] = "%$category%";
    $types .= "s";
}

$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Prepare and bind
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

// Execute and get result
$stmt->execute();
$result = $stmt->get_result();

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

$stmt->close();
$conn->close();
?>
