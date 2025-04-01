<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

// API for the item donation
if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data received"]);
        exit();
    }

    // Validate required fields
    if (
        empty($data["user_id"]) ||  // User ID is required
        empty($data["item_name"]) || 
        empty($data["item_condition"]) || 
        empty($data["user_section"]) || 
        empty($data["number_of_items"]) || 
        empty($data["image_filename"])  // Changed from item_image to image_filename
    ) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit();
    }

    $user_id = intval($data["user_id"]);
    $item_name = $data["item_name"];
    $item_condition = $data["item_condition"]; // New or Used
    $user_section = $data["user_section"]; // Donor or NGO
    $number_of_items = intval($data["number_of_items"]);
    $image_filename = $data["image_filename"]; // Now storing the image filename instead of URI

    // Validate item condition (must be 'New' or 'Used')
    if (!in_array($item_condition, ['new', 'used'])) {
        echo json_encode(["status" => "error", "message" => "Invalid item condition"]);
        exit();
    }

    // Validate user section (must be 'Donor' or 'NGO'])
    if (!in_array($user_section, ['Donor', 'NGO'])) {
        echo json_encode(["status" => "error", "message" => "Invalid user section"]);
        exit();
    }

    // Fetch the username from users table
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    $username = $user["username"];
    $stmt->close();

    // Insert into donated_items table
    $stmt = $conn->prepare("INSERT INTO donated_items (user_id, username, item_name, item_condition, user_section, number_of_items, item_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssis", $user_id, $username, $item_name, $item_condition, $user_section, $number_of_items, $image_filename);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Item added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add item"]);
    }

    $stmt->close();
    $conn->close();
}

// Get User Specific Donations
if ($method == "GET") {
    if (isset($_GET["user_id"])) {
        $userId = intval($_GET["user_id"]);

        $sql = "SELECT * FROM donated_items WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $donations = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $ngoId = intval($row["ngo_id"]);

                // Fetch NGO details
                $ngoSql = "SELECT phonenumber, address FROM ngos WHERE ngo_id = ?";
                $ngoStmt = mysqli_prepare($conn, $ngoSql);

                if ($ngoStmt) {
                    mysqli_stmt_bind_param($ngoStmt, "i", $ngoId);
                    mysqli_stmt_execute($ngoStmt);
                    $ngoResult = mysqli_stmt_get_result($ngoStmt);

                    if ($ngoRow = mysqli_fetch_assoc($ngoResult)) {
                        $row["phonenumber"] = $ngoRow["phonenumber"];
                        $row["address"] = $ngoRow["address"];
                    } else {
                        $row["phonenumber"] = null;
                        $row["address"] = null;
                    }
                    mysqli_stmt_close($ngoStmt);
                }

                $donations[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $donations]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to prepare statement"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is required"]);
    }
}
if ($method == "DELETE") {
    // Try to read JSON body
    $data = json_decode(file_get_contents("php://input"), true);

    // If JSON is empty, try to get from URL parameters
    if ($data === null) {
        $item_id = isset($_GET["item_id"]) ? intval($_GET["item_id"]) : null;
    } else {
        $item_id = isset($data["item_id"]) ? intval($data["item_id"]) : null;
    }

    if (!$item_id) {
        echo json_encode(["status" => "error", "message" => "Donation ID is required"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM donated_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Donation deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete donation"]);
    }

    $stmt->close();
    $conn->close();
}
?>