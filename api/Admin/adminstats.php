<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ( $method == "GET" ) {
    $usersql = "SELECT * FROM users";
    $ngosql = "SELECT * FROM ngos";
    $totalItems = "SELECT * FROM donated_items";
    $finalDonation = "SELECT * FROM donated_items WHERE status = 'Completed'";

    $userResult = mysqli_query($conn, $usersql);
    $ngoResult = mysqli_query($conn, $ngosql);
    $totalItemsResult = mysqli_query($conn, $totalItems);
    $finalDonationResult = mysqli_query($conn, $finalDonation);

    if ($userResult || $ngoResult || $totalItemsResult || $finalDonationResult) {
        $userCount = mysqli_num_rows($userResult);
        $ngoCount = mysqli_num_rows($ngoResult);
        $totalItemsCount = mysqli_num_rows($totalItemsResult);
        $finalDonationCount = mysqli_num_rows($finalDonationResult);

        echo json_encode([
            "status" => "success",
            "userCount" => $userCount,
            "ngoCount" => $ngoCount,
            "totalItemsCount" => $totalItemsCount,
            "finalDonationCount" => $finalDonationCount
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to fetch records"
        ]);
    }
}
?>