<?php 
    $servername = "localhost";
    $Username = "root";
    $Password = "";
    $database = "dayabhav";

    $conn = mysqli_connect($servername, $Username, $Password, $database);

    if(!$conn){
        echo json_encode(["status" => "error", "message" => "Could not connect to database"]);
    }
?>