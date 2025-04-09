<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input["item_id"]) || !isset($input["status"])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $item_id = $input["item_id"];
    $status = $input["status"];
    
    // Check if ngo_id and ngoname are provided in the request
    if (isset($input["ngo_id"]) && isset($input["ngoname"])) {
        $ngo_id = $input["ngo_id"];
        $ngoname = $input["ngoname"];
        
        // Generate current timestamp for accepted_date
        $accepted_date = date("Y-m-d H:i:s");
        
        // Update with NGO information and accepted_date
        $sql = "UPDATE donated_items SET status = ?, ngo_id = ?, ngoname = ?, accepted_date = ? WHERE item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssi", $status, $ngo_id, $ngoname, $accepted_date, $item_id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Donation status, NGO details, and accepted date updated",
                    "accepted_date" => $accepted_date
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update donation status and NGO details"]);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error", "message" => "Database query failed"]);
        }
    } else {
        // Update status only
        $sql = "UPDATE donated_items SET status = ? WHERE item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $status, $item_id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(["status" => "success", "message" => "Donation status updated"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update donation status"]);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error", "message" => "Database query failed"]);
        }
    }
} 
else if ($method == "PUT") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input["user_id"]) || !isset($input["status"]) || !isset($input["ngo_id"]) || !isset($input["ngoname"])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $user_id = $input["user_id"];
    $status = $input["status"];
    $ngo_id = $input["ngo_id"];
    $ngoname = $input["ngoname"];
    
    // Generate current timestamp for accepted_date
    $accepted_date = date("Y-m-d H:i:s");

    $sql = "UPDATE donated_items SET ngo_id = ?, ngoname = ?, status = ?, accepted_date = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssi", $ngo_id, $ngoname, $status, $accepted_date, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                "status" => "success", 
                "message" => "Donation status updated with accepted date",
                "accepted_date" => $accepted_date
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update donation status"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Database query failed"]);
    }
} 

mysqli_close($conn);
?>