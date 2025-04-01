<?php 
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    
    require("../config/db.php");
    
    $method = $_SERVER['REQUEST_METHOD'];

    if($method == "GET"){
        $sql = "SELECT * FROM donated_items WHERE status = 'pending'";
        $result = mysqli_query($conn, $sql);

        if($result){
            $donations = [];

            while($row = mysqli_fetch_assoc($result)){
                $donations[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $donations]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to fetch donations"]);
        }
    }
?>
