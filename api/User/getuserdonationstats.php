<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    if (isset($_GET['user_id'])) {  
        $userId = intval($_GET['user_id']);

        // Prepare statement to count rows
        $stmt = $conn->prepare("SELECT COUNT(*) FROM donated_items WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
            
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch(); 
            echo json_encode(["status" => "success", "count" => $count]);
        } else {
            echo json_encode(["status" => "error", "message" => "Query execution failed"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is required in URL"]);
    }
}
?>
