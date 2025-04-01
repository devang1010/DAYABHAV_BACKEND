<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    // Get NGO ID from POST data
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input["ngo_id"]) && !isset($_POST["ngo_id"]) && !isset($_GET["ngo_id"])) {
        echo json_encode(["status" => "error", "message" => "NGO ID is required"]);
        exit();
    }
    
    // Check all possible sources for ngo_id
    $ngoId = isset($input["ngo_id"]) ? intval($input["ngo_id"]) : 
             (isset($_POST["ngo_id"]) ? intval($_POST["ngo_id"]) : 
             (isset($_GET["ngo_id"]) ? intval($_GET["ngo_id"]) : 0));

    // Fetch accepted requests
    $sqlAccepted = "SELECT * FROM inventory WHERE ngo_id = ? AND status = 'Accepted'";
    $stmtAccepted = $conn->prepare($sqlAccepted);
    $stmtAccepted->bind_param("i", $ngoId);
    $acceptedItems = [];

    if ($stmtAccepted->execute()) {
        $resultAccepted = $stmtAccepted->get_result();
        $acceptedCount = $resultAccepted->num_rows;
        while ($row = $resultAccepted->fetch_assoc()) {
            $acceptedItems[] = $row;
        }
    } else {
        $acceptedCount = 0;
    }

    // Fetch completed requests
    $sqlCompleted = "SELECT * FROM inventory WHERE ngo_id = ? AND status = 'completed'";
    $stmtCompleted = $conn->prepare($sqlCompleted);
    $stmtCompleted->bind_param("i", $ngoId);
    $completedItems = [];

    if ($stmtCompleted->execute()) {
        $resultCompleted = $stmtCompleted->get_result();
        $completedCount = $resultCompleted->num_rows;
        while ($row = $resultCompleted->fetch_assoc()) {
            $completedItems[] = $row;
        }
    } else {
        $completedCount = 0;
    }

    echo json_encode([
        "status" => "success",
        "accepted_count" => $acceptedCount,
        "completed_count" => $completedCount,
        "accepted_data" => $acceptedItems,
        "completed_data" => $completedItems
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>