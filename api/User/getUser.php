<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    if (isset($_GET['user_id'])) {
        $userId = intval($_GET['user_id']); // Sanitize input

        // Query the user data
        $stmt = $conn->prepare("SELECT user_id, username, email, phonenumber, city, country, created_at, blocked FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode(["status" => "success", "user" => $user]);
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "No user ID provided"]);
    }

    $conn->close();
}
?>
