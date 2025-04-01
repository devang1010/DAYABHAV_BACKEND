<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "DELETE") {
    if (isset($_GET['user_id'])) {  // Check in URL parameters
        $userId = intval($_GET['user_id']);

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

        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete user"]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is required in URL"]);
    }
}
?>
