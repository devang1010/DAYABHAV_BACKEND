<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    $sql = "SELECT * FROM users";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $users = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }

        echo json_encode(["status" => "success", "data" => $users]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error Getting Users"]);
    }
}
?>