<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "PUT" || $method == "POST") {
    // Get input data
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if we have item_id or user_id
    if (isset($input["item_id"]) && isset($input["status"])) {
        $item_id = $input["item_id"];
        $status = $input["status"];

        // Update donated_items table using item_id
        $sql = "UPDATE donated_items SET status = ? WHERE item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $status, $item_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $affected_rows = mysqli_stmt_affected_rows($stmt);
                
                if ($affected_rows > 0) {
                    echo json_encode([
                        "status" => "success", 
                        "message" => "Donation status updated successfully",
                        "affected_rows" => $affected_rows
                    ]);
                } else {
                    echo json_encode([
                        "status" => "warning", 
                        "message" => "No records were updated. Item ID might not exist.",
                        "affected_rows" => 0
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Failed to update donation status: " . mysqli_stmt_error($stmt)
                ]);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Database query preparation failed: " . mysqli_error($conn)
            ]);
        }
    } 
    // If we don't have item_id, try using user_id
    else if (isset($input["user_id"]) && isset($input["status"])) {
        $user_id = $input["user_id"];
        $status = $input["status"];

        // Update donated_items table using user_id
        $sql = "UPDATE donated_items SET status = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $status, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $affected_rows = mysqli_stmt_affected_rows($stmt);
                
                if ($affected_rows > 0) {
                    echo json_encode([
                        "status" => "success", 
                        "message" => "Donation status updated successfully",
                        "affected_rows" => $affected_rows
                    ]);
                } else {
                    echo json_encode([
                        "status" => "warning", 
                        "message" => "No records were updated. User ID might not exist.",
                        "affected_rows" => 0
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Failed to update donation status: " . mysqli_stmt_error($stmt)
                ]);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Database query preparation failed: " . mysqli_error($conn)
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Missing required fields. Either item_id or user_id is required."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Invalid request method. Use PUT or POST."
    ]);
}

mysqli_close($conn);
?>