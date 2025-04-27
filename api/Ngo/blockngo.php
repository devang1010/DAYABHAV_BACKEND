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

    if (isset($data["ngo_id"])) {
        $ngoId = $data["ngo_id"];

        // Sanitize the input
        $ngoId = mysqli_real_escape_string($conn, $ngoId);

        // Prepare the update query
        $query = "UPDATE ngos SET blocked = 1 WHERE ngo_id = '$ngoId'";

        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => true, "message" => "NGO blocked successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to block NGO."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "NGO ID not provided."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed."]);
}
?>