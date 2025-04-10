<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    // Get the input JSON from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["user_id"])) {
        $userid = $data["user_id"];

        // Sanitize the input
        $userid = mysqli_real_escape_string($conn, $userid);

        // Prepare the update query
        $query = "UPDATE users SET blocked = 0 WHERE user_id = '$userid'";

        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => true, "message" => "User unblocked successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to unblock user."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User ID not provided."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed."]);
}
?>