<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require("./config/db.php");

$method = $_SERVER["REQUEST_METHOD"];

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data === null) {
        echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
        exit();
    }

    if (empty($data["email"]) || empty($data["password"])) {
        echo json_encode(["success" => false, "message" => "Email and password required"]);
        exit();
    }

    $email = trim($data["email"]);
    $password = md5(trim($data["password"]));

    // Check Admin Table FIRST
    $sql = "SELECT admin_id, role_id FROM admins WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            "success" => true,
            "message" => "Admin login successful",
            "admin_id" => $row["admin_id"],
            "role_id" => $row["role_id"]
        ]);
        exit();
    }

    // Check user table
    $sql = "SELECT user_id, username, email, role_id, phonenumber, city, country, blocked FROM users WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            "success" => true,
            "message" => "User login successful",
            "user_id" => $row["user_id"],
            "role_id" => $row["role_id"],
            "username" => $row["username"],
            "email" => $row["email"],
            "phonenumber" => $row["phonenumber"],
            "city" => $row["city"],
            "country" => $row["country"],
            "blocked" => $row["blocked"],
        ]);
        exit();
    }

    // Check NGO table
    $sql = "SELECT ngo_id, ngoname, email, role_id, blocked FROM ngos WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            "success" => true,
            "message" => "NGO login successful",
            "ngo_id" => $row["ngo_id"],
            "role_id" => $row["role_id"],
            "ngoname" => $row["ngoname"],
            "email" => $row["email"],
            "blocked" => $row["blocked"],
        ]);
        exit();
    }

    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit();
}
?>