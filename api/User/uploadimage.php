<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require("../config/db.php");

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = "../../view/uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Debug: Log the received files
error_log("FILES: " . print_r($_FILES, true));

// Check if image was uploaded
if (!isset($_FILES['image'])) {
    echo json_encode(["status" => "error", "message" => "No image uploaded"]);
    exit();
}

// Get the uploaded file information
$file = $_FILES['image'];

// Check for errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
        UPLOAD_ERR_NO_FILE => "No file was uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
    ];
    $errorMessage = isset($errorMessages[$file['error']]) ? $errorMessages[$file['error']] : "Unknown upload error";
    echo json_encode(["status" => "error", "message" => $errorMessage, "error_code" => $file['error']]);
    exit();
}

// Check file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
if (!in_array($file['type'], $allowedTypes) && !empty($file['type'])) {
    echo json_encode(["status" => "error", "message" => "Only JPEG, PNG, and GIF images are allowed", "type" => $file['type']]);
    exit();
}

// Generate unique filename - use the filename from the request if available
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
// If no extension is provided, try to determine it from the mime type
if (empty($fileExtension)) {
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];
    $fileExtension = isset($mimeToExt[$file['type']]) ? $mimeToExt[$file['type']] : 'jpg';
}

$uniqueFilename = time() . '_' . uniqid() . '.' . $fileExtension;
$targetPath = $uploadDir . $uniqueFilename;

// Move the uploaded file to the upload directory
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Set appropriate permissions
    chmod($targetPath, 0644);
    
    // Return success response with the filename
    echo json_encode([
        "status" => "success",
        "message" => "Image uploaded successfully",
        "filename" => $uniqueFilename,
        "path" => $targetPath
    ]);
} else {
    // Return error response with more details
    echo json_encode([
        "status" => "error", 
        "message" => "Failed to upload image",
        "details" => [
            "source" => $file['tmp_name'],
            "destination" => $targetPath,
            "writable" => is_writable($uploadDir),
            "exists" => file_exists($uploadDir)
        ]
    ]);
}
?>