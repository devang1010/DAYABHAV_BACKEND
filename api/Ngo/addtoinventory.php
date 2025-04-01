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

// Get the request body
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    !isset($data['ngo_id']) ||
    !isset($data['user_id']) ||
    !isset($data['username']) ||
    !isset($data['ngoname']) ||
    !isset($data['item_name']) ||
    !isset($data['quantity']) ||
    !isset($data['status']) ||
    !isset($data['item_id'])
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

// First check if the item_id already exists in the inventory
$checkQuery = "SELECT COUNT(*) as count FROM inventory WHERE item_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("i", $data['item_id']);
$checkStmt->execute();
$result = $checkStmt->get_result();
$row = $result->fetch_assoc();
$checkStmt->close();

// If item_id already exists, return an error
if ($row['count'] > 0) {
    echo json_encode(["status" => "error", "message" => "Item already exists in inventory"]);
    exit();
}

// Item doesn't exist, proceed with insertion
$query = "INSERT INTO inventory (ngo_id, user_id, username, ngoname, item_name, quantity, status, item_id)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param(
    "iisssssi",
    $data['ngo_id'],
    $data['user_id'],
    $data['username'],
    $data['ngoname'],
    $data['item_name'],
    $data['quantity'],
    $data['status'],
    $data['item_id']
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Item added to inventory"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add item to inventory"]);
}

$stmt->close();
$conn->close();
?>