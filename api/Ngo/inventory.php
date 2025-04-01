<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];


// Get all the items of the specific NGO
if ($method == "GET") {
    if (!isset($_GET['ngo_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing ngo_id"]);
        exit;
    }

    $ngo_id = $_GET['ngo_id'];

    $sql = "SELECT * FROM inventory WHERE ngo_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $ngo_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $inventory = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $user_id = $row['user_id']; // Fetch user_id from inventory

                // Query to get city and phone_number from users table
                $user_sql = "SELECT city, phonenumber, email FROM users WHERE user_id = ?";
                $user_stmt = mysqli_prepare($conn, $user_sql);

                if ($user_stmt) {
                    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
                    mysqli_stmt_execute($user_stmt);
                    $user_result = mysqli_stmt_get_result($user_stmt);

                    if ($user_row = mysqli_fetch_assoc($user_result)) {
                        $row['city'] = $user_row['city'];
                        $row['phonenumber'] = $user_row['phonenumber'];
                        $row['email'] = $user_row['email'];
                    } else {
                        $row['city'] = null;
                        $row['phonenumber'] = null;
                        $row['email'] = null;
                    }

                    mysqli_stmt_close($user_stmt);
                }

                $inventory[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $inventory]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to fetch inventory"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
}

// status completed in the inventory table
elseif ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['inventory_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing inventory_id"]);
        exit;
    }

    $inventory_id = $data['inventory_id'];
    $new_status = "completed";

    $update_sql = "UPDATE inventory SET status = ? WHERE inventory_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "si", $new_status, $inventory_id);
        if (mysqli_stmt_execute($update_stmt)) {
            echo json_encode(["status" => "success", "message" => "Inventory status updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update status"]);
        }
        mysqli_stmt_close($update_stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
}

mysqli_close($conn);
