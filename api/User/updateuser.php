<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "PUT") {
    // Read JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if all required fields are provided
    if (!isset($data['user_id'], $data['email'], $data['city'], $data['country'], $data['phonenumber'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $userId = intval($data['user_id']);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $city = htmlspecialchars(strip_tags($data['city']));
    $country = htmlspecialchars(strip_tags($data['country']));
    $phonenumber = htmlspecialchars(strip_tags($data['phonenumber']));

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET email = ?, city = ?, country = ?, phonenumber = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $email, $city, $country, $phonenumber, $userId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update user"]);
    }

    $stmt->close();
    $conn->close();
}
?>
