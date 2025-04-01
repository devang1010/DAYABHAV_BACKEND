<?php
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");

    require("../config/db.php");

    $method = $_SERVER['REQUEST_METHOD'];

    // User Registration
    if ($method == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON data received"]);
            exit();
        }

        if (empty($data["username"]) || empty($data["email"]) || empty($data["phonenumber"]) || empty($data["city"]) || empty($data["country"]) || empty($data["password"])) {
            echo json_encode(["status" => "error", "message" => "All fields are required"]);
            exit();
        }

        $username = trim($data["username"]);
        $email = trim($data["email"]);
        $phonenumber = trim($data["phonenumber"]);
        $city = trim($data["city"]);
        $country = trim($data["country"]);
        $password = md5(trim($data["password"]));

        $role_id = 2; 

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Invalid email format"]);
            exit();
        }

        // Check if email already exists
        $checkEmailSql = "SELECT user_id FROM users WHERE email = ?";
        $checkStmt = mysqli_prepare($conn, $checkEmailSql);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            echo json_encode(["status" => "error", "message" => "Email already exists"]);
            exit();
        }

        $sql = "INSERT INTO users (username, email, phonenumber, city, country, password, role_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssi", $username, $email, $phonenumber, $city, $country, $password, $role_id);

        if (mysqli_stmt_execute($stmt)) {
            http_response_code(200); // Ensure success response
            echo json_encode(["success" => true, "message" => "Registration successful"]);
            exit();
        } else {
            http_response_code(500); // Server error response
            echo json_encode(["success" => false, "message" => "Registration failed"]);
            exit();
        }
        
    }
?>
