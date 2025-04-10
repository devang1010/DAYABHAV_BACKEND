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

    if (empty($data["email"]) || empty($data["new_password"])) {
        echo json_encode(["success" => false, "message" => "Email and new password required"]);
        exit();
    }

    $email = trim($data["email"]);
    $new_password = md5(trim($data["new_password"]));
    
    // Initialize response - we'll assume failure until we find a matching record
    $response = ["success" => false, "message" => "Email not found in our records"];

    // First, check admins table
    $sql = "SELECT admin_id FROM admins WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Admin found, update password
        $update_sql = "UPDATE admins SET password = ? WHERE email = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $new_password, $email);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $response = ["success" => true, "message" => "Admin password has been reset successfully"];
        } else {
            $response = ["success" => false, "message" => "Unable to reset admin password: " . mysqli_error($conn)];
        }
        mysqli_stmt_close($update_stmt);
    } else {
        // Check users table
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            // User found, update password
            $update_sql = "UPDATE users SET password = ? WHERE email = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ss", $new_password, $email);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $response = ["success" => true, "message" => "User password has been reset successfully"];
            } else {
                $response = ["success" => false, "message" => "Unable to reset user password: " . mysqli_error($conn)];
            }
            mysqli_stmt_close($update_stmt);
        } else {
            // Check NGOs table
            $sql = "SELECT ngo_id FROM ngos WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                // NGO found, update password
                $update_sql = "UPDATE ngos SET password = ? WHERE email = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ss", $new_password, $email);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $response = ["success" => true, "message" => "NGO password has been reset successfully"];
                } else {
                    $response = ["success" => false, "message" => "Unable to reset NGO password: " . mysqli_error($conn)];
                }
                mysqli_stmt_close($update_stmt);
            }
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    echo json_encode($response);
    exit();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}
?>