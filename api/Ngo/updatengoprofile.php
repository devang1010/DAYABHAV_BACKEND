<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "PUT") {
    // Read JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if all required fields are provided
    if (!isset($data['ngo_id'], $data['email'], $data['address'], $data['phonenumber'], $data['ngoname'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $ngoId = intval($data['ngo_id']);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(strip_tags($data['address']));
    $phonenumber = htmlspecialchars(strip_tags($data['phonenumber']));
    $ngoname = htmlspecialchars(strip_tags($data['ngoname']));

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

    // Update NGO information
    $stmt = $conn->prepare("UPDATE ngos SET ngoname = ?, email = ?, address = ?, phonenumber = ? WHERE ngo_id = ?");
    $stmt->bind_param("ssssi", $ngoname, $email, $address, $phonenumber, $ngoId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "NGO profile updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update NGO profile"]);
    }

    $stmt->close();
    $conn->close();
}
?>
