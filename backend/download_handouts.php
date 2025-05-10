<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the raw request
file_put_contents('request.log', date('Y-m-d H:i:s') . " - " . file_get_contents("php://input") . "\n", FILE_APPEND);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resources";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
    file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $error . "\n", FILE_APPEND);
    die(json_encode(["error" => $error]));
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log received data
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Received data: " . print_r($data, true) . "\n", FILE_APPEND);

// Validate input
if (!$data || !isset($data['course_name'])) {
    $error = "Invalid request data or missing course name";
    file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $error . "\n", FILE_APPEND);
    die(json_encode(["error" => $error, "received_data" => $data]));
}

$courseName = trim($data['course_name']);
$chapterNumber = isset($data['chapter']) ? (int)$data['chapter'] : null;

// Prepare query to find the file
$query = "SELECT file_path, file_name FROM handouts_table 
          WHERE course_name LIKE CONCAT('%', ?, '%') 
          AND (chapter = ? OR ? IS NULL)
          ORDER BY chapter LIMIT 1";

$stmt = $conn->prepare($query);
if (!$stmt) {
    $error = "Prepare failed: " . $conn->error;
    file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $error . "\n", FILE_APPEND);
    die(json_encode(["error" => $error]));
}

$stmt->bind_param("sii", $courseName, $chapterNumber, $chapterNumber);

if (!$stmt->execute()) {
    $error = "Execute failed: " . $stmt->error;
    file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $error . "\n", FILE_APPEND);
    die(json_encode(["error" => $error]));
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $filePath = $row['file_path'];
    
    // Verify file exists and is readable
    if (file_exists($filePath) && is_readable($filePath)) {
        $response = [
            "download_url" => $filePath,
            "file_name" => $row['file_name']
        ];
        file_put_contents('success.log', date('Y-m-d H:i:s') . " - Downloaded: " . $filePath . "\n", FILE_APPEND);
        echo json_encode($response);
    } else {
        $error = "File not found or not readable: " . $filePath;
        file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $error . "\n", FILE_APPEND);
        echo json_encode(["error" => $error]);
    }
} else {
    $error = "No matching handout found for course: " . $courseName . ($chapterNumber ? " chapter " . $chapterNumber : "");
    file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
}

$stmt->close();
$conn->close();
?>