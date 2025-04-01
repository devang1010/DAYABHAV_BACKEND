<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "DELETE") {
    // Read JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if NGO ID is provided
    if (!isset($data['ngo_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing NGO ID"]);
        exit();
    }

    $ngoId = intval($data['ngo_id']);

    // Check if the NGO exists
    $stmt = $conn->prepare("SELECT ngo_id FROM ngos WHERE ngo_id = ?");
    $stmt->bind_param("i", $ngoId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "NGO not found"]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Delete the NGO
    $stmt = $conn->prepare("DELETE FROM ngos WHERE ngo_id = ?");
    $stmt->bind_param("i", $ngoId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "NGO deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete NGO"]);
    }

    $stmt->close();
    $conn->close();
}
?>
