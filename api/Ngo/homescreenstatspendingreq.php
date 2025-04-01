<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    // Get all pending requests from inventory table
    $sql = "SELECT * FROM donated_items WHERE status = 'pending'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $count = mysqli_num_rows($result);
        $items = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "count" => $count,
            "data" => $items
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to fetch pending records"
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>