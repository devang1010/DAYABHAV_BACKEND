<?php
// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");

// Check if filename parameter is set
if (!isset($_GET['filename']) || empty($_GET['filename'])) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Filename parameter is required"]);
    exit();
}

$filename = $_GET['filename'];
$uploadDir = "../../view/uploads/";
$filePath = $uploadDir . $filename;

// Validate the filename to prevent directory traversal attacks
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Invalid filename"]);
    exit();
}
    
// Check if file exists
if (!file_exists($filePath)) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Image not found"]);
    exit();
}

// Set the appropriate content type based on file extension
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
switch ($extension) {
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        break;
    case 'png':
        header('Content-Type: image/png');
        break;
    case 'gif':
        header('Content-Type: image/gif');
        break;
    default:
        header('Content-Type: application/octet-stream');
}

// Output the file
readfile($filePath);
exit();
?>